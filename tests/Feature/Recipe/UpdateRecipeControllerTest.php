<?php

namespace Tests\Feature\Recipe;

use App\Http\Controllers\Recipe\UpdateRecipeController;
use App\Http\Requests\Recipe\UpdateRecipeRequest;
use App\Models\Recipe;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;

class UpdateRecipeControllerTest extends RecipeControllerTest
{
    public function test_it_updates_recipe()
    {
        Storage::fake('public');

        $oldFile = UploadedFile::fake()->image('old.jpg');
        $oldPath = 'recipes/' . $oldFile->hashName();
        Storage::disk('public')->putFileAs('recipes', $oldFile, $oldFile->hashName());

        $newFile = UploadedFile::fake()->image('new.jpg');
        $expectedNewPath = "recipes/" . $newFile->hashName();

        $recipe = Recipe::factory()->create([
            'user_id' => $this->user->id,
            'picture' => $oldPath,
            'name' => 'Original Name',
        ]);

        $request = Request::create(
            "/api/v1/recipes/{$recipe->id}",
            'PUT',
            [
                'name' => 'Updated Name',
                'cuisine_type' => 'Italian',
                'ingredients' => '...',
                'steps' => '...',
            ],
            [],
            ['picture' => $newFile]
        );

        $formRequest = UpdateRecipeRequest::createFrom($request);
        $formRequest->setContainer($this->app);
        $formRequest->setUserResolver(fn() => $this->user);
        $formRequest->validateResolved();

        $updatedRecipe = clone $recipe;
        $updatedRecipe->name = 'Updated Name';
        $updatedRecipe->picture = $expectedNewPath;

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
            ->with(Mockery::type(Recipe::class), [
                'name' => 'Updated Name',
                'cuisine_type' => 'Italian',
                'ingredients' => '...',
                'steps' => '...',
                'picture' => $expectedNewPath,
            ])
            ->andReturn(true);

        $controller = new UpdateRecipeController($this->repositoryMock, $this->app->make(ResponseService::class));

        $response = $controller($formRequest, $recipe->id);

        $this->assertEquals(200, $response->getStatusCode());
        $json = $response->getData();

        $this->assertEquals('Updated Name', $json->data->name);
        $this->assertEquals($expectedNewPath, $json->data->picture);

        $this->assertFalse(Storage::disk('public')->exists($oldPath));
        $this->assertTrue(Storage::disk('public')->exists($expectedNewPath));
    }
}
