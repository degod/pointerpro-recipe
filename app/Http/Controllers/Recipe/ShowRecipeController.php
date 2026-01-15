<?php

namespace App\Http\Controllers\Recipe;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Repositories\Recipe\RecipeRepositoryInterface;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/v1/recipes/{id}",
 *     tags={"Recipes"},
 *     summary="Get a specific recipe",
 *     description="Retrieve a single recipe by ID. Only the owner can access it.",
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the recipe to retrieve",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Recipe retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="user_id", type="integer", example=5),
 *             @OA\Property(property="name", type="string", example="Spaghetti Carbonara"),
 *             @OA\Property(property="cuisine_type", type="string", example="Italian"),
 *             @OA\Property(property="ingredients", type="string", example="200g pasta\n4 eggs\n100g pancetta"),
 *             @OA\Property(property="steps", type="string", example="1. Boil pasta\n2. Fry pancetta\n3. Mix with eggs"),
 *             @OA\Property(property="picture", type="string", nullable=true, example="recipes/abc123.jpg"),
 *             @OA\Property(property="visibility", type="string", example="public"),
 *             @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-05T10:00:00Z"),
 *             @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-05T12:30:00Z")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden - You do not own this recipe"),
 *     @OA\Response(response=404, description="Recipe not found")
 * )
 */
class ShowRecipeController extends Controller
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

        return $this->response->success(200, 'Recipe retrieved', $recipe->toArray());
    }
}
