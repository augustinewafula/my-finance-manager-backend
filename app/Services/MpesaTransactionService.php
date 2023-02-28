<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Transaction;
use BenSampo\Enum\Rules\EnumValue;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MpesaTransactionService
{
    public function decodeMpesaTransactionMessage(string $message): array
    {
        $type = $this->getTransactionType($message);
        $messageArray = Str::of($message)->explode(' ');
        $referenceCode = $messageArray[0];

        $amountStr = Str::of($message)->after('Ksh')->before(' sent to');
        $amount = (int) str_replace(',', '', $amountStr);

        Log::info("message: $message");
        Log::info("type: $type");

        switch ($type) {
            case TransactionType::SENT:
            case TransactionType::PAID:
                $subject = Str::betweenFirst($message, ' to ', ' on');
                break;
            case TransactionType::RECEIVED:
                $subject = Str::betweenFirst($message, ' from ', ' on');
                break;
            case TransactionType::WITHDRAW:
                $subject = Str::betweenFirst($message, ' from ', 'Ksh');
                break;
            default:
                if (Str::contains($message, 'airtime')) {
                    $subject = Str::betweenFirst($message, ' of ', ' on');
                } elseif (Str::contains($message, 'balance was')) {
                    $subject = 'ignore balance';
                } else {
                    $subject = 'Unknown';
                }
                break;
        }

        $subject = Str::of($subject)->trim()->toString();
        $dateStr = Str::between($message, 'on ', ' at');
        $timeStr = Str::of($message)->after('at ')->beforeLast('.');
        $timeStr = Str::of($timeStr)->before('New')->trim()->toString();
        $timeStr = Str::of($timeStr)->replace('.', '')->toString();

        Log::info("subject: $subject");
        Log::info("date: $dateStr");
        Log::info("time: $timeStr");

        $transactionCost = $this->getTransactionCost($message);

        Log::info("transaction_cost: $transactionCost");

        $fullDateStr = "$dateStr $timeStr";
        $fullDate = Carbon::createFromFormat('d/m/y g:i A', $fullDateStr)->toDateTimeString();

        return [
            'reference_code' => $referenceCode,
            'type' => $type,
            'amount' => $amount,
            'subject' => $subject,
            'date' => $fullDate,
            'transaction_cost' => $transactionCost,
        ];
    }


    private function getTransactionType(String $message): int
    {
        $contains_sent = Str::of($message)->contains('sent');
        if ($contains_sent) {
            return TransactionType::SENT;
        }
        $contains_received = Str::of($message)->contains('received');
        if ($contains_received) {
            return TransactionType::RECEIVED;
        }
        $contains_withdraw = Str::of($message)->contains('Withdraw');
        if ($contains_withdraw) {
            return TransactionType::WITHDRAW;
        }
        $contains_paid = Str::of($message)->contains('paid');
        if ($contains_paid) {
            return TransactionType::PAID;
        }

        return TransactionType::UNKNOWN;
    }

    private function getTransactionCost($message): ?string
    {
        $contains_transaction_cost = Str::of($message)->contains('Transaction cost');
        if ($contains_transaction_cost) {
            $transaction_cost = Str::betweenFirst($message, 'Transaction cost,', '.');
            $transaction_cost = Str::of($transaction_cost)->remove('Ksh')->trim()->toString();

            return Str::of($transaction_cost)->remove(',')->toString();
        }

        return null;
    }

    /**
     * @throws Exception
     */
    public function validateDecodedMpesaTransactionMessage($decoded_message): void
    {
        $validator = Validator::make($decoded_message, [
            'reference_code' => 'required|string|unique:transactions|max:15',
            'type' => ['required', new EnumValue(TransactionType::class)],
            'amount' => 'required|numeric|min:1',
            'subject' => ['required', 'string', 'max:255', Rule::notIn(['ignore balance'])],
            'date' => 'required|date',
            'transaction_cost' => 'nullable|numeric|min:0',
        ], ['reference_code.unique' => 'Transaction already exists']);
        if ($validator->fails()) {
            Log::info($validator->errors());
            throw new ValidationException($validator);
        }
    }

    /**
     * @throws Exception
     */
    public function store($message, $decodeMessage, $identifyMpesaTransactionCategory): Transaction
    {
        $category = $identifyMpesaTransactionCategory->execute($decodeMessage['subject']);
        return Transaction::create([
            'reference_code' => $decodeMessage['reference_code'],
            'type' => $decodeMessage['type'],
            'amount' => $decodeMessage['amount'],
            'subject' => $decodeMessage['subject'],
            'message' => $message,
            'date' => $decodeMessage['date'],
            'transaction_category_id' => $category['category_id'],
            'transaction_sub_category_id' => $category['sub_category_id'],
            'transaction_cost' => $decodeMessage['transaction_cost'],
        ]);

    }

}
