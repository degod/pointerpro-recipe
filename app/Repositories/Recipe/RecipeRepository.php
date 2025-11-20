<?php

namespace App\Repositories\Recipe;

use App\Enums\UserRole;
use App\Enums\Visibility;
use App\Models\Recipe;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class RecipeRepository implements RecipeRepositoryInterface
{
    public function __construct(
        protected Recipe $recipeModel
    ) {}

    public function all(): LengthAwarePaginator
    {
        // $cacheKey = 'recipes';

        // return Cache::remember($cacheKey, 300, function () {
        return $this->recipeModel->orderBy('id', 'DESC')->paginate(config('pagination.default.per_page'));
        // });
    }

    public function findByUser(int $userId): LengthAwarePaginator
    {
        // $cacheKey = 'recipes:' . md5(json_encode("userId=" . $userId));

        // return Cache::remember($cacheKey, 300, function () use ($userId) {
        return $this->recipeModel->where(['user_id' => $userId])->orderBy('id', 'DESC')->paginate(config('pagination.default.per_page'));
        // });
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
        // $cacheKey = 'recipes:' . md5(json_encode("filters=" . $filters));

        // return Cache::remember($cacheKey, 300, function () use ($filters) {
        $role = auth('sanctum')->user()->role;
        $userId = auth('sanctum')->user()->id;

        return $this->recipeModel
            ->when($filters['name'] ?? null, function ($query, $name) {
                $query->where('name', 'LIKE', "%{$name}%");
            })
            ->when($filters['cuisine_type'] ?? null, function ($query, $type) {
                $query->where('cuisine_type', 'LIKE', "%{$type}%");
            })
            ->when($role, function ($query) use ($role, $userId) {
                if ($role == UserRole::ADMIN) {
                    $query->whereIn('visibility', [Visibility::PRIVATE, Visibility::PUBLIC]);
                } else {
                    $query->whereIn('visibility', [Visibility::PUBLIC])->orWhere('user_id', $userId);
                }
            })
            ->orderBy('created_at', 'DESC')
            ->paginate(config('pagination.default.per_page'));
        // });
    }
}
