<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    public function test_unauthenticated_user_cannot_access_transactions(): void
    {
        $response = $this->get('/api/v1/transactions', [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_transactions(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->get('/api/v1/transactions', [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_create_transactions(): void
    {
        $response = $this->post('/api/v1/transactions', [
            'name' => 'Test transaction',
        ], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_transactions(): void
    {
        $response = $this->createTransaction();

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'subject',
            'amount',
            'created_at',
            'updated_at'
        ]);
        $response->assertJson([
            'subject' => 'Test Transaction',
        ]);
    }

    public function test_authenticated_user_can_create_mpesa_transactions(): void
    {
        $mpesaTransactions  = $this->mpesaTransactions();
        $this->seed();

        foreach ($mpesaTransactions as $mpesaTransaction) {
            $response = $this->actingAs($this->user, 'sanctum')->post('/api/v1/mpesa-transactions', [
                'message' => $mpesaTransaction['message'],
            ], [
                'Accept' => 'application/json'
            ]);

            $response->assertStatus(201);
            $response->assertJsonStructure([
                'id',
                'subject',
                'amount',
                'created_at',
                'updated_at'
            ]);
            Log::info($response->json());
            $response->assertJsonFragment([
                'reference_code' => $mpesaTransaction['reference'],
                'subject' => $mpesaTransaction['subject'],
                'amount' => $mpesaTransaction['amount'],
                'type' => $mpesaTransaction['type'],
                'transaction_cost' => $mpesaTransaction['transaction_cost'],
//                'date' => Carbon::parse($mpesaTransaction['transaction_date'])->format('Y-m-d\TH:i:s.u\Z'),
            ]);
        }

    }

    public function test_unauthenticated_user_cannot_update_transactions(): void
    {
        $response = $this->put('/api/v1/transactions/1', [
            'name' => 'Test transaction',
        ], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_update_transactions(): void
    {
        $response = $this->createTransaction();
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'subject',
            'amount',
            'created_at',
            'updated_at'
        ]);
        $transaction = $response->json();
        $response = $this->actingAs($this->user, 'sanctum')->put('/api/v1/transactions/' . $transaction['id'], [
            'name' => 'Test transaction updated',
        ], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['transaction' => [
            'id',
            'subject',
            'amount',
            'created_at',
            'updated_at'
        ]]);
        $response->assertJson(['transaction' =>[
            'subject' => 'Test Transaction Updated',
        ]]);
    }

    public function test_unauthenticated_user_cannot_delete_transactions(): void
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer invalid_token',
        ])->delete('/api/v1/transactions/1');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_delete_transactions(): void
    {
        $response = $this->createTransaction();
        $transactionId = $response->json('id');
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'subject',
            'amount',
            'created_at',
            'updated_at'
        ]);
        $transaction = $response->json();
        $response = $this->actingAs($this->user, 'sanctum')->delete('/api/v1/transactions/' . $transaction['id'], [
            'Accept' => 'application/json'
        ]);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('transactions', [
            'id' => $transactionId
        ]);
    }

    private function createTransaction(): \Illuminate\Testing\TestResponse
    {
        $transactionCategoryResponse = $this->createTransactionCategory();

        return $this->actingAs($this->user, 'sanctum')->post('/api/v1/transactions', [
            'name' => 'Test transaction',
            'amount' => 100,
            'transaction_category_id' => $transactionCategoryResponse->json('id'),
            'transaction_type' => 1,
            'transaction_date' => now()->toDateTimeString(),
        ], [
            'Accept' => 'application/json'
        ]);
    }

    private function mpesaTransactions(): array
    {
        return [
            [
                'message' => 'QID1X3J8M1 Confirmed. Ksh880.00 sent to GRICON  NZOMO 0746912034 on 13/9/22 at 9:34 PM. New M-PESA balance is Ksh2,914.34. Transaction cost, Ksh12.00. Amount you can transact within the day is 288,590.00. Pay for goods & Services using Lipa Na M-Pesa! To reverse, forward this message to 456.',
                'subject' => 'Gricon  Nzomo 0746912034',
                'amount' => 880,
                'reference' => 'QID1X3J8M1',
                'transaction_cost' => '12',
                'type' => TransactionType::SENT,
                'transaction_date' => '2022-09-13 21:34:00',
            ],
            [
                'message' => 'QIJ2A5XQ6C Confirmed. Ksh80.00 paid to DAYLIGHT HOTEL. on 19/9/22 at 5:02 PM.New M-PESA balance is Ksh112.45. Transaction cost, Ksh0.00. Amount you can transact within the day is 294,973.00. Pay with M-PESA GlobalPay virtual Visa card linked to MPESA wallet. Click https://bit.ly/3LQTXIT',
                'subject' => 'Daylight Hotel.',
                'amount' => 80,
                'reference' => 'QIJ2A5XQ6C',
                'transaction_cost' => '0',
                'type' => TransactionType::PAID,
                'transaction_date' => '2022-09-19 17:02:00',
            ],
            [
                'message' => 'QJ62CC6DPE Confirmed. You have received Ksh40,000.00 from IM BANK LIMITED- APP on 6/10/22 at 9:42 AM. New M-PESA balance is Ksh43,450.01. Buy goods with M-PESA.',
                'subject' => 'Im Bank Limited- App',
                'amount' => 40000,
                'reference' => 'QJ62CC6DPE',
                'transaction_cost' => null,
                'type' => TransactionType::RECEIVED,
                'transaction_date' => '2022-09-19 17:02:00',
            ],
            [
                'message' => 'QIS3SU4975 Confirmed.on 28/9/22 at 10:01 AMWithdraw Ksh400.00 from 2088761 - Nemsik Ventures Ltd Feic Investment New M-PESA balance is Ksh5,070.32. Transaction cost, Ksh27.00. Amount you can transact within the day is 299,600.00. Dial *334# >My account to get your free stamped statements.',
                'subject' => '2088761 - Nemsik Ventures Ltd Feic Investment',
                'amount' => 400,
                'reference' => 'QIS3SU4975',
                'transaction_cost' => '27',
                'type' => TransactionType::WITHDRAW,
                'transaction_date' => '2022-09-19 17:02:00',
            ],
        ];
    }

    private function createTransactionCategory(): TestResponse
    {
        return $this->actingAs($this->user, 'sanctum')->post('/api/v1/transaction-categories', [
            'name' => 'Test transaction category',
        ], [
            'Accept' => 'application/json'
        ]);
    }

    private function createUser(): User
    {
        return User::factory()->create(['password' => bcrypt('password')]);
    }
}
