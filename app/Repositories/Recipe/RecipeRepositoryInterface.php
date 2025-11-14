<?php

namespace App\Repositories\Recipe;

use App\Models\Recipe;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RecipeRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Recipe;
    public function create(array $data): Recipe;
    public function update(Recipe $recipe, array $data): bool;
    public function delete(Recipe $recipe): bool;
    public function findByUser(int $userId): Collection;
}
