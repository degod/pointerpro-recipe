<?php

namespace Tests\Feature\Recipe;

use App\Http\Controllers\Recipe\UpdateRecipeController;
use App\Http\Requests\Recipe\UpdateRecipeRequest;
use App\Models\Recipe;
use App\Services\ResponseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Storage;
use Mockery;

class UpdateRecipeControllerTest extends RecipeControllerTest
{
    public function test_it_updates_recipe()
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = 'recipes/' . $oldFile->hashName();
        $newFile = UploadedFile::fake()->image('new.jpg');

        $recipe = Recipe::factory()->create([
            'user_id' => $this->user->id,
            'picture' => $oldPath,
            'name' => 'Original Name',
        ]);

        Storage::disk('public')->putFileAs('recipes', $oldFile, $oldFile->hashName());
        $request = \Illuminate\Http\Request::create(
            "/api/v1/recipes/{$recipe->id}",
            'PUT',
            ['name' => 'Updated Name'],
            [],
            ['picture' => $newFile]
        );
        $formRequest = UpdateRecipeRequest::createFrom($request);
        $formRequest->setContainer($this->app);
        $formRequest->setUserResolver(fn() => $this->user);

        $route = new Route('PUT', '/api/v1/recipes/{recipe}', []);
        $route->parameters = ['recipe' => $recipe, 'id' => $recipe->id];
        $formRequest->setRouteResolver(fn() => $route);
        $formRequest->setValidator($this->app['validator']->make($formRequest->all(), $formRequest->rules()));
        $formRequest->validateResolved();
        $uploadedPath = $formRequest->file('picture')->store('recipes', 'public');
        $expectedData = [
            'name' => 'Updated Name',
            'picture' => $uploadedPath,
        ];

        $updatedRecipe = $recipe->replicate();
        $updatedRecipe->fill($expectedData);
        $updatedRecipe->exists = true;
        $mockedRecipe = Mockery::mock($recipe)->makePartial();
        $mockedRecipe->shouldReceive('fresh')->andReturn($updatedRecipe);

        $this->repositoryMock
            ->shouldReceive('find')
            ->once()
            ->with($recipe->id)
            ->andReturn($mockedRecipe);

        $this->repositoryMock
            ->shouldReceive('update')
            ->once()
            ->with($mockedRecipe, $expectedData)
            ->andReturn(true);

        $this->responseService = $this->app->make(ResponseService::class);
        $controller = new UpdateRecipeController($this->repositoryMock, $this->responseService);
        $response = $controller($formRequest, $recipe->id);
        $this->assertEquals(200, $response->getStatusCode());

        $json = $response->getData();
        $this->assertEquals('success', $json->status);
        $this->assertEquals('Recipe updated successfully', $json->message);
        $this->assertEquals('Updated Name', $json->data->name);
        $this->assertEquals($uploadedPath, $json->data->picture);

        $this->assertFalse(Storage::disk('public')->exists($oldPath));
        $this->assertTrue(Storage::disk('public')->exists($uploadedPath));
    }
}
