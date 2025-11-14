<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_logs_in_a_user_successfully(): void
    {
        User::factory()->create([
            'email' => 'janedoe@example.com',
            'password' => 'secret123',
        ]);
        $payload = [
            'email' => 'janedoe@example.com',
            'password' => 'secret123',
        ];

        $response = $this->postJson(route('user.login'), $payload);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'User logged in successfully',
                'data' => [
                    'email' => 'janedoe@example.com',
                    'role' => 'user',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'janedoe@example.com',
            'role' => 'user',
        ]);
    }

    public function test_it_fails_login_with_wrong_credentials(): void
    {
        User::factory()->create([
            'email' => 'right@email.com',
            'password' => 'secret123',
        ]);

        $payload = [
            'email' => 'right@email.com',
            'password' => 'wrong456',
        ];

        $response = $this->postJson(route('user.login'), $payload);

        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Invalid email or password',
            ]);
    }

    public function test_it_fails_login_with_wrong_email(): void
    {
        User::factory()->create([
            'email' => 'right@email.com',
            'password' => 'secret123',
        ]);

        $payload = [
            'email' => 'wrong@email.com',
            'password' => 'wrong456',
        ];

        $response = $this->postJson(route('user.login'), $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed',
            ]);
    }
}
