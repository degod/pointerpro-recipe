<?php

namespace App\Repositories\Recipe;

use App\Models\Recipe;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RecipeRepository implements RecipeRepositoryInterface
{
    public function __construct(
        protected Recipe $recipeModel
    ) {}

    public function all(): Collection
    {
        return $this->recipeModel->all();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->recipeModel->paginate($perPage);
    }

    public function find(int $id): ?Recipe
    {
        return $this->recipeModel->find($id);
    }

    public function create(array $data): Recipe
    {
        return $this->recipeModel->create($data);
    }

    public function update(Recipe $recipe, array $data): bool
    {
        return $recipe->update($data);
    }

    public function delete(Recipe $recipe): bool
    {
        return $recipe->delete();
    }

    public function findByUser(int $userId): Collection
    {
        return $this->recipeModel->where('user_id', $userId)->get();
    }
}
