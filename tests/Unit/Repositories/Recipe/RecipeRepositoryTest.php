<?php

namespace Tests\Unit\Repositories\Recipe;

use App\Models\Recipe;
use App\Models\User;
use App\Repositories\Recipe\RecipeRepository;
use App\Repositories\Recipe\RecipeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class RecipeRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected RecipeRepository $repository;
    protected User $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->app->make(RecipeRepositoryInterface::class);
        $this->user = User::factory()->create();
    }

    public function test_can_create_recipe()
    {
        $data = [
            'user_id' => $this->user->id,
            'name' => 'Spaghetti Carbonara',
            'cuisine_type' => 'Italian',
            'ingredients' => "200g pasta\n4 eggs\n100g pancetta",
            'steps' => "1. Boil pasta\n2. Fry pancetta\n3. Mix eggs",
            'picture' => null,
        ];

        $recipe = $this->repository->create($data);

        $this->assertInstanceOf(Recipe::class, $recipe);
        $this->assertEquals('Spaghetti Carbonara', $recipe->name);
        $this->assertDatabaseHas('recipes', ['name' => 'Spaghetti Carbonara']);
    }

    public function test_can_find_recipe_by_id()
    {
        $recipe = Recipe::factory()->create(['user_id' => $this->user->id]);

        $found = $this->repository->find($recipe->id);

        $this->assertEquals($recipe->id, $found->id);
        $this->assertEquals($recipe->name, $found->name);
    }

    public function test_find_returns_null_for_nonexistent_id()
    {
        $found = $this->repository->find(999);

        $this->assertNull($found);
    }

    public function test_can_update_recipe()
    {
        $recipe = Recipe::factory()->create(['user_id' => $this->user->id]);

        $updated = $this->repository->update($recipe, [
            'name' => 'Updated Recipe',
            'cuisine_type' => 'Mexican',
        ]);

        $this->assertTrue($updated);
        $this->assertEquals('Updated Recipe', $recipe->fresh()->name);
        $this->assertEquals('Mexican', $recipe->fresh()->cuisine_type);
    }

    public function test_can_delete_recipe()
    {
        $recipe = Recipe::factory()->create(['user_id' => $this->user->id]);

        $deleted = $this->repository->delete($recipe);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
    }

    public function test_can_get_all_recipes()
    {
        Recipe::factory(3)->create(['user_id' => $this->user->id]);

        $recipes = $this->repository->all();

        $this->assertCount(3, $recipes);
        $this->assertInstanceOf(Collection::class, $recipes);
    }

    public function test_can_paginate_recipes()
    {
        Recipe::factory(20)->create(['user_id' => $this->user->id]);

        $paginated = $this->repository->paginate(10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginated);
        $this->assertCount(10, $paginated->items());
        $this->assertEquals(20, $paginated->total());
    }

    public function test_can_find_recipes_by_user()
    {
        $otherUser = User::factory()->create();

        Recipe::factory(2)->create(['user_id' => $this->user->id]);
        Recipe::factory(1)->create(['user_id' => $otherUser->id]);

        $userRecipes = $this->repository->findByUser($this->user->id);

        $this->assertCount(2, $userRecipes);
        $this->assertTrue($userRecipes->every(fn($r) => $r->user_id === $this->user->id));
    }
}
