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
 *     path="/api/v1/recipes",
 *     tags={"Recipes"},
 *     summary="List authenticated user's recipes",
 *     description="Retrieve all recipes created by the authenticated user. Pagination is not included in this endpoint.",
 *     security={{"sanctum":{}}},
 *
 *     @OA\Response(
 *         response=200,
 *         description="List of user's recipes",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 type="object",
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=5),
 *                 @OA\Property(property="name", type="string", example="Spaghetti Carbonara"),
 *                 @OA\Property(property="cuisine_type", type="string", example="Italian"),
 *                 @OA\Property(property="ingredients", type="string", example="200g pasta\n4 eggs\n100g pancetta"),
 *                 @OA\Property(property="steps", type="string", example="1. Boil pasta\n2. Fry pancetta\n3. Mix with eggs"),
 *                 @OA\Property(property="picture", type="string", nullable=true, example="recipes/abc123.jpg"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-04-05T10:00:00Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-04-05T12:30:00Z")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated")
 * )
 */
class IndexRecipeController extends Controller
{
    public function __construct(
        private RecipeRepositoryInterface $recipeRepo,
        private ResponseService $response
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(): JsonResponse
    {
        $userId = auth('sanctum')->id();
        $role = auth('sanctum')->user()->role;

        if ($role == UserRole::ADMIN) {
            $recipes = $this->recipeRepo->all();
        } else {
            $recipes = $this->recipeRepo->findByUser($userId);
        }

        return $this->response->success(200, 'Recipes retrieved', $recipes->items());
    }
}
