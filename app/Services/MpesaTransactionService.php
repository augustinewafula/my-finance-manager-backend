<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\MpesaTransaction;
use BenSampo\Enum\Rules\EnumValue;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MpesaTransactionService
{
    public function decodeMpesaTransactionMessage($message): array
    {
        $message_array = Str::of($message)->explode(' ');
        $reference_code = $message_array[0];

        $sliced_message_as_from_ksh = Str::of($message)->after('Ksh');
        $sliced_message_array_as_from_ksh = Str::of($sliced_message_as_from_ksh)->explode(' ');

        $amount = $sliced_message_array_as_from_ksh[0];
        $amount = Str::of($amount)->remove(',')->toString();

        $type = $sliced_message_array_as_from_ksh[1];

        $subject = Str::betweenFirst($message, 'to', 'on');
        $subject = Str::squish($subject);

        $date = Str::betweenFirst($message, '. on', 'at');
        $date = Str::of($date)->trim()->toString();

        $time = Str::betweenFirst($message, 'at', '.');
        $time = Str::of($time)->trim()->toString();

        $full_date = Carbon::createFromFormat('d/m/y g:i A', "$date $time")->toDateTimeString();

        $type = match ($type) {
            'paid' => TransactionType::PAID,
            'sent' => TransactionType::SENT,
            'received' => TransactionType::RECEIVED,
            'withdraw' => TransactionType::WITHDRAW,
            default => TransactionType::UNKNOWN,
        };

        return [
            'reference_code' => $reference_code,
            'type' => $type,
            'amount' => $amount,
            'subject' => $subject,
            'date' => $full_date,
        ];

    }

    /**
     * @throws Exception
     */
    public function validateDecodedMpesaTransactionMessage($decoded_message): void
    {
        $validator = Validator::make($decoded_message, [
            'reference_code' => 'required|string|unique:mpesa_transactions|max:15',
            'type' => ['required', new EnumValue(TransactionType::class)],
            'amount' => 'required|numeric|min:1',
            'subject' => 'required|string|max:50',
            'date' => 'required|date',
        ], ['reference_code.unique' => 'Transaction already exists']);
        if ($validator->fails()) {
            Log::info($validator->errors());
            throw new ValidationException($validator);
        }
    }

    /**
     * @throws Exception
     */
    public function store($message, $decodeMessage, $identifyMpesaTransactionCategory): MpesaTransaction
    {
        $category = $identifyMpesaTransactionCategory->execute($decodeMessage['subject']);
        return MpesaTransaction::create([
            'reference_code' => $decodeMessage['reference_code'],
            'type' => $decodeMessage['type'],
            'amount' => $decodeMessage['amount'],
            'subject' => $decodeMessage['subject'],
            'message' => $message,
            'date' => $decodeMessage['date'],
            'transaction_category_id' => $category['category_id'],
            'transaction_sub_category_id' => $category['sub_category_id'],
        ]);

    }

}
