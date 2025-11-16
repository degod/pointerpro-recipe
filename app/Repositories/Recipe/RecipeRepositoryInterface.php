<?php

namespace App\Repositories\Recipe;

use App\Models\Recipe;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RecipeRepositoryInterface
{
    public function all(): LengthAwarePaginator;
    public function findByUser(int $userId): LengthAwarePaginator;
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    public function find(int $id): ?Recipe;
    public function create(array $data): Recipe;
    public function update(Recipe $recipe, array $data): bool;
    public function delete(Recipe $recipe): bool;
    public function filterRecipes(array $filters): LengthAwarePaginator;
}
