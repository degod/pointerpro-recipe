<?php

namespace Tests\Feature\Recipe;

use App\Http\Controllers\Recipe\StoreRecipeController;
use App\Http\Requests\Recipe\StoreRecipeRequest;
use App\Models\Recipe;
use App\Services\ResponseService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;

class StoreRecipeControllerTest extends RecipeControllerTest
{
    public function test_it_creates_a_recipe_successfully()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('carbonara.jpg');

        $request = StoreRecipeRequest::create('/api/v1/recipes', 'POST', [
            'name'         => 'Spaghetti Carbonara',
            'cuisine_type' => 'Italian',
            'ingredients'  => "200g pasta\n4 eggs",
            'steps'        => "1. Boil\n2. Mix",
        ], [], ['picture' => $file]);

        $request->setContainer($this->app);
        $request->setUserResolver(fn() => $this->user);
        $request->setValidator(
            $this->app['validator']->make($request->all(), $request->rules())
        );
        $request->validateResolved();
        $storedPath = $file->store('recipes', 'public');

        $expectedData = array_merge($request->validated(), [
            'user_id' => $this->user->id,
            'picture' => $storedPath,
        ]);
        $recipeModel = Recipe::make($expectedData);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($arg) use ($expectedData) {
                return $arg['name']         === $expectedData['name']
                    && $arg['cuisine_type'] === $expectedData['cuisine_type']
                    && $arg['ingredients']  === $expectedData['ingredients']
                    && $arg['steps']        === $expectedData['steps']
                    && $arg['user_id']      === $expectedData['user_id']
                    && str_starts_with($arg['picture'], 'recipes/');
            }))
            ->andReturn($recipeModel);

        $controller = new StoreRecipeController(
            $this->repositoryMock,
            $this->app->make(ResponseService::class)
        );
        $response = $controller($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertTrue(Storage::disk('public')->exists($storedPath));

        $json = $response->getData(true);

        $this->assertEquals('success', $json['status']);
        $this->assertEquals('Recipe created successfully', $json['message']);
        $this->assertEquals('Spaghetti Carbonara', $json['data']['name']);
        $this->assertEquals($this->user->id, $json['data']['user_id']);
        $this->assertStringStartsWith('recipes/', $json['data']['picture']);
    }
}
