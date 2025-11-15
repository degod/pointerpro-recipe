<?php

namespace Tests\Feature\Http\Controllers\Recipe;

use App\Http\Controllers\Recipe\FilterRecipeController;
use App\Models\Recipe;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;

class FilterRecipeControllerTest extends RecipeControllerTest
{
    public function test_it_filters_recipes()
    {
        $recipes = Recipe::factory()->count(2)->make()->toArray();
        $paginated = new LengthAwarePaginator($recipes, 2, 10, 1);
        $filters = [
            'name' => 'spa',
            'cuisine_type' => 'Italian'
        ];
        $this->repositoryMock
            ->shouldReceive('filterRecipes')
            ->once()
            ->with(Mockery::on(function ($filters) {
                return $filters['name'] === 'spa'
                    && $filters['cuisine_type'] === 'Italian';
            }))
            ->andReturn($paginated);

        $request = Request::create(
            '/api/v1/recipes',
            'GET',
            [
                'name' => 'spa',
                'cuisine_type' => 'Italian',
            ]
        );

        $controller = new FilterRecipeController(
            $this->repositoryMock,
            $this->app->make(ResponseService::class)
        );
        $response = $controller($request);

        $this->assertEquals(200, $response->getStatusCode());
        $json = $response->getData();

        $this->assertEquals(true, $json->success);
        $this->assertEquals('Recipes fetched successfully', $json->message);
        $this->assertIsArray($json->data);
        $this->assertCount(2, $json->data);
        $this->assertEquals('Italian', $filters['cuisine_type']);
        $this->assertStringContainsString('spa', $filters['name']);
    }
}
