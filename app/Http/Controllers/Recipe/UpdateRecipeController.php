<?php

namespace App\Http\Controllers\Recipe;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Recipe\UpdateRecipeRequest;
use App\Repositories\Recipe\RecipeRepositoryInterface;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;

/**
 * @OA\Put(
 *     path="/api/v1/recipes/{id}",
 *     tags={"Recipes"},
 *     summary="Update a recipe",
 *     description="Update a recipe owned by the authenticated user. All fields are optional.",
 *     security={{"sanctum":{}}},
 *
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the recipe to update",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Partial recipe data (only fields to update)",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 @OA\Property(property="name", type="string", example="Updated Carbonara", description="New name"),
 *                 @OA\Property(property="cuisine_type", type="string", example="Italian", description="New cuisine"),
 *                 @OA\Property(property="ingredients", type="string", example="300g pasta\n5 eggs\n150g pancetta", description="New ingredients"),
 *                 @OA\Property(property="steps", type="string", example="1. Boil more pasta\n2. Fry extra pancetta", description="New steps"),
 *                 @OA\Property(property="picture", type="string", format="binary", description="Optional: Replace image (deletes old one)"),
 *                 @OA\Property(property="visibility", type="string", description="Replace visibility")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=200,
 *         description="Recipe updated successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="user_id", type="integer", example=5),
 *             @OA\Property(property="name", type="string", example="Updated Carbonara"),
 *             @OA\Property(property="cuisine_type", type="string", example="Italian"),
 *             @OA\Property(property="ingredients", type="string", example="300g pasta\n5 eggs\n150g pancetta"),
 *             @OA\Property(property="steps", type="string", example="1. Boil more pasta\n2. Fry extra pancetta"),
 *             @OA\Property(property="picture", type="string", nullable=true, example="recipes/xyz789.jpg"),
 *             @OA\Property(property="visibility", type="string", example="public"),
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=403, description="Forbidden - Not owner"),
 *     @OA\Response(response=404, description="Recipe not found"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
class UpdateRecipeController extends Controller
{
    public function __construct(
        private RecipeRepositoryInterface $recipeRepo,
        private ResponseService $response
    ) {}

    public function __invoke(UpdateRecipeRequest $request, int $id): JsonResponse
    {
        $recipe = $this->recipeRepo->find($id);

        if (!$recipe) {
            return $this->response->error(404, 'Recipe not found');
        }

        if ($recipe->user_id !== auth('sanctum')->id() && auth('sanctum')->user()->role !== UserRole::ADMIN) {
            return $this->response->error(403, 'Unauthorized');
        }

        $data = $request->validated();

        if ($request->hasFile('picture')) {
            if ($recipe->picture) {
                Storage::disk('public')->delete($recipe->picture);
            }
            $data['picture'] = $request->file('picture')->store('recipes', 'public');
        }

        $this->recipeRepo->update($recipe, $data);

        return $this->response->success(200, 'Recipe updated successfully', $recipe->fresh()->toArray());
    }
}
