<?php

namespace App\Services;

use App\Actions\IdentifyMpesaTransactionCategory;
use App\Enums\TransactionType;
use App\Models\MpesaTransaction;
use BenSampo\Enum\Rules\EnumValue;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
        ], ['reference_code.unique' => 'Transaction already exists']);
        if ($validator->fails()) {
            Log::info($validator->errors());
            throw new Exception($validator->errors()->first());
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
            'transaction_category_id' => $category['category_id'],
            'transaction_sub_category_id' => $category['sub_category_id'],
        ]);

    }

}
