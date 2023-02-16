<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    public function test_user_can_register(): void
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@gmail.com',
            'password' => bcrypt('password'),
            'password_confirmation' => bcrypt('password')
        ];
        $response = $this->postJson('/api/v1/register', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('users', Arr::except($data, ['password', 'password_confirmation']));

        $response->assertJsonStructure([
            'token',
            'user' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at'
            ]
        ]);
        $user = User::find($response->json('user.id'));

//        $this->assertAuthenticatedAs($user, 'sanctum');
    }

    public  function test_user_can_login(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => $this->user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200);
        $this->assertAuthenticatedAs($this->user, 'sanctum');

    }

    private function createUser(): User
    {
        return User::factory()->create(['password' => bcrypt('password')]);
    }
}
