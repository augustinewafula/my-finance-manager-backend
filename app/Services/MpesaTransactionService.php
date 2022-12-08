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
    public function decodeMpesaTransactionMessage($message): array
    {
        $type = $this->getTransactionType($message);
        $message_array = Str::of($message)->explode(' ');
        $reference_code = $message_array[0];

        $sliced_message_as_from_ksh = Str::of($message)->after('Ksh');
        $sliced_message_array_as_from_ksh = Str::of($sliced_message_as_from_ksh)->explode(' ');

        $amount = $sliced_message_array_as_from_ksh[0];
        $amount = Str::of($amount)->remove(',')->toString();

        Log::info('message: '. $message);
        Log::info('type: ' . $type);

        if ($type === TransactionType::SENT || $type === TransactionType::PAID) {
            $subject = Str::betweenFirst($message, ' to ', ' on ');
            $date = Str::betweenFirst($message, ' on ', ' at ');
            $time = Str::betweenFirst($message, ' at ', '.');
            if (strlen($time) > 8) {
                $time = Str::betweenFirst($message, ' at ', ' New');
            }
        } elseif ($type === TransactionType::RECEIVED) {
            $subject = Str::betweenFirst($message, ' from ', ' on ');
            $date = Str::betweenFirst($message, ' on ', ' at ');
            $time = Str::of($message)->betweenFirst(' at ', 'New ')->remove('.');
        } elseif ($type === TransactionType::WITHDRAW) {
            $subject = Str::betweenFirst($message, ' from ', 'New ');
            $date = Str::betweenFirst($message, '.on ', ' at ');
            $time = Str::betweenFirst($message, ' at ', 'Withdraw');
        } elseif (Str::of($message)->contains('airtime')) {
            $subject = Str::betweenFirst($message, ' of ', ' on ');
            $date = Str::betweenFirst($message, ' on ', ' at ');
            $time = Str::of($message)->betweenFirst(' at ', 'New ')->remove('.');
        } elseif (Str::of($message)->contains('balance was')) {
            $subject = 'ignore balance';
            $date = Str::betweenFirst($message, ' on ', ' at ');
            $time = Str::of($message)->betweenFirst(' at ', 'Send ')->remove('.');
        } else {
            $subject = 'Unknown';
            $date = Carbon::now()->format('d/m/y');
            $time = Carbon::now()->format('g:i A');
        }
        $subject = Str::squish($subject);
        $date = Str::of($date)->trim()->toString();
        $time = Str::of($time)->trim()->toString();
        $transaction_cost = $this->getTransactionCost($message);
        Log::info('subject: ' . $subject);
        Log::info('date: ' . $date);
        Log::info('time: ' . $time);
        Log::info('transaction_cost: ' . $transaction_cost);

        $full_date = Carbon::createFromFormat('d/m/y g:i A', "$date $time")->toDateTimeString();

        return [
            'reference_code' => $reference_code,
            'type' => $type,
            'amount' => $amount,
            'subject' => $subject,
            'date' => $full_date,
            'transaction_cost' => $transaction_cost,
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
