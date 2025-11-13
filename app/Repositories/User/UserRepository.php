<?php

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Support\Collection;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(private User $user) {}

    public function getAllUsers(): Collection
    {
        return $this->user->all();
    }

    public function findById(int $id): ?User
    {
        return $this->user->find($id);
    }

    public function findByUuid(string $uuid): ?User
    {
        return $this->user->where('uuid', $uuid)->first();
    }

    public function findByEmail(string $email): ?User
    {
        return $this->user->where('email', $email)->first();
    }

    public function create(array $data): User
    {
        return $this->user->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
