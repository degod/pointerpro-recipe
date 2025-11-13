<?php

namespace Tests\Feature\Http\Controllers\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterUserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_registers_a_new_user_successfully(): void
    {
        $payload = [
            'name' => 'Jane Doe',
            'email' => 'janedoe@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->postJson(route('user.register'), $payload);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'User registered successfully',
                'data' => [
                    'name' => 'Jane Doe',
                    'email' => 'janedoe@example.com',
                    'role' => 'user',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'janedoe@example.com',
            'name' => 'Jane Doe'
        ]);
    }

    public function test_it_fails_registration_when_email_is_taken(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $payload = [
            'name' => 'John Doe',
            'email' => 'taken@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->postJson(route('user.register'), $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed',
            ]);
    }

    public function test_it_fails_registration_when_password_confirmation_does_not_match(): void
    {
        $payload = [
            'name' => 'Mismatch',
            'email' => 'mismatch@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'different123',
        ];

        $response = $this->postJson(route('user.register'), $payload);

        $response->assertStatus(422)
            ->assertJson([
                'status' => 'error',
                'message' => 'Validation failed',
            ]);
    }
}
