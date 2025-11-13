<?php

namespace Tests\Unit\Repositories\User;

use Tests\TestCase;
use App\Models\User;
use App\Repositories\User\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private UserRepository $userRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new UserRepository(new User());
    }

    public function test_it_can_create_a_user(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
            'role' => 'user'
        ];

        $user = $this->userRepo->create($data);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('testuser@example.com', $user->email);
    }

    public function test_it_can_find_user_by_email(): void
    {
        $user = User::factory()->create(['email' => 'findme@example.com']);

        $found = $this->userRepo->findByEmail('findme@example.com');

        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_it_can_update_a_user(): void
    {
        $user = User::factory()->create();
        $updated = $this->userRepo->update($user, ['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $updated->name);
    }

    public function test_it_can_delete_a_user(): void
    {
        $user = User::factory()->create();
        $result = $this->userRepo->delete($user);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
