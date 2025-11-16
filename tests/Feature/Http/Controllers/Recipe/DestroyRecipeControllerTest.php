<?php

namespace Tests\Feature\Http\Controllers\Recipe;

use App\Http\Controllers\Recipe\DestroyRecipeController;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DestroyRecipeControllerTest extends RecipeControllerTest
{
    public function test_it_deletes_owned_recipe()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('delete.jpg');

        $recipe = \App\Models\Recipe::factory()->create([
            'user_id' => $this->user->id,
            'picture' => 'recipes/' . $file->hashName(),
        ]);

        Storage::disk('public')->putFileAs('recipes', $file, $file->hashName());
        $this->repositoryMock
            ->shouldReceive('find')
            ->once()
            ->with($recipe->id)
            ->andReturn($recipe);

        $this->repositoryMock
            ->shouldReceive('delete')
            ->once()
            ->with($recipe)
            ->andReturnUsing(fn($r) => $r->delete());

        $controller = new DestroyRecipeController($this->repositoryMock, $this->responseService);

        $response = $controller($recipe->id);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertFalse(Storage::disk('public')->exists($recipe->picture));
        $this->assertDatabaseMissing('recipes', ['id' => $recipe->id]);
    }
}
