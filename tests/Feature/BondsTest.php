<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BondsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    public function test_unauthenticated_user_cannot_access_bonds(): void
    {
        $response = $this->get('/api/v1/bonds', [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_bonds(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->get('/api/v1/bonds', [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
    }

    private function createUser(): User
    {
        return User::factory()->create(['password' => bcrypt('password')]);
    }
}
