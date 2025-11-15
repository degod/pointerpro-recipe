<?php

namespace App\Repositories\Recipe;

use App\Models\Recipe;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RecipeRepository implements RecipeRepositoryInterface
{
    public function __construct(
        protected Recipe $recipeModel
    ) {}

    public function all(): LengthAwarePaginator
    {
        return $this->recipeModel->orderBy('id', 'DESC')->paginate(3);
    }

    public function findByUser(int $userId): LengthAwarePaginator
    {
        return $this->recipeModel->where(['user_id' => $userId])->orderBy('id', 'DESC')->paginate(config('pagination.default.per_page'));
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->recipeModel->paginate($perPage ?? config('pagination.default.per_page'));
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

    public function filterRecipes(array $filters): LengthAwarePaginator
    {
        return $this->recipeModel
            ->when($filters['name'] ?? null, function ($query, $name) {
                $query->where('name', 'LIKE', "%{$name}%");
            })
            ->when($filters['cuisine_type'] ?? null, function ($query, $type) {
                $query->where('cuisine_type', 'LIKE', "%{$type}%");
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(config('pagination.default.per_page'));
    }
}
