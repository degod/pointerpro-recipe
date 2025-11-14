<?php

namespace App\Http\Controllers\Recipe;

use App\Http\Controllers\Controller;
use App\Repositories\Recipe\RecipeRepositoryInterface;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

/**
 * @OA\Delete(
 *     path="/api/v1/recipes/{id}",
 *     tags={"Recipes"},
 *     summary="Delete a recipe",
 *     description="Permanently delete a recipe. Only the owner can delete it. The associated picture is also removed from storage.",
 *     security={{"sanctum":{}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the recipe to delete",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=204,
 *         description="Recipe deleted successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Recipe deleted")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden - You do not own this recipe"),
 *     @OA\Response(response=404, description="Recipe not found")
 * )
 */
class DestroyRecipeController extends Controller
{
    public function __construct(
        private RecipeRepositoryInterface $recipeRepo,
        private ResponseService $response
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(int $id): JsonResponse
    {
        $recipe = $this->recipeRepo->find($id);

        if (!$recipe) {
            return $this->response->error(404, 'Recipe not found');
        }

        if ($recipe->user_id !== auth('sanctum')->id()) {
            return $this->response->error(403, 'Unauthorized');
        }

        if ($recipe->picture) {
            Storage::disk('public')->delete($recipe->picture);
        }

        $this->recipeRepo->delete($recipe);

        return $this->response->success(204, 'Recipe deleted');
    }
}
