<?php

namespace Tests\Feature\Http\Controllers\Recipe;

use App\Http\Controllers\Recipe\IndexRecipeController;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class IndexRecipeControllerTest extends RecipeControllerTest
{
    public function test_it_lists_user_recipes()
    {
        $items = Collection::make([
            \App\Models\Recipe::factory()->make(['user_id' => $this->user->id]),
            \App\Models\Recipe::factory()->make(['user_id' => $this->user->id]),
        ]);

        $userRecipes = new LengthAwarePaginator(
            $items,
            $items->count(),
            15,
            1,
            ['path' => request()->url()]
        );

        $this->repositoryMock
            ->shouldReceive('findByUser')
            ->once()
            ->with($this->user->id)
            ->andReturn($userRecipes);

        $controller = new IndexRecipeController($this->repositoryMock, $this->responseService);

        // dd(\DB::connection()->getDatabaseName());
        $response = $controller();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(2, json_decode($response->getContent())->data);
    }
}
