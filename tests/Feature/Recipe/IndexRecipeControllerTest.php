<?php

namespace Tests\Feature\Recipe;

use App\Http\Controllers\Recipe\IndexRecipeController;
use Illuminate\Database\Eloquent\Collection;

class IndexRecipeControllerTest extends RecipeControllerTest
{
    public function test_it_lists_user_recipes()
    {
        $userRecipes = Collection::make([
            \App\Models\Recipe::factory()->make(['user_id' => $this->user->id]),
            \App\Models\Recipe::factory()->make(['user_id' => $this->user->id]),
        ]);

        $this->repositoryMock
            ->shouldReceive('findByUser')
            ->once()
            ->with($this->user->id)
            ->andReturn($userRecipes);

        $controller = new IndexRecipeController($this->repositoryMock, $this->responseService);

        $response = $controller();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(2, json_decode($response->getContent())->data);
    }
}
