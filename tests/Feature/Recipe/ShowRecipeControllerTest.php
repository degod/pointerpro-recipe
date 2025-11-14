<?php

namespace Tests\Feature\Recipe;

use App\Http\Controllers\Recipe\ShowRecipeController;
use App\Models\Recipe;

class ShowRecipeControllerTest extends RecipeControllerTest
{
    public function test_it_shows_owned_recipe()
    {
        $recipe = Recipe::factory()->make(['user_id' => $this->user->id]);

        $this->repositoryMock
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($recipe);

        $controller = new ShowRecipeController($this->repositoryMock, $this->responseService);

        $response = $controller(1);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($recipe->name, json_decode($response->getContent())->data->name);
    }

    public function test_it_returns_403_for_other_user_recipe()
    {
        $recipe = Recipe::factory()->make(['user_id' => $this->otherUser->id]);

        $this->repositoryMock
            ->shouldReceive('find')
            ->once()
            ->with(1)
            ->andReturn($recipe);

        $controller = new ShowRecipeController($this->repositoryMock, $this->responseService);

        $response = $controller(1);

        $this->assertEquals(403, $response->getStatusCode());
    }
}
