<?php

namespace App\Http\Controllers\Recipe;

use App\Http\Controllers\Controller;
use App\Repositories\Recipe\RecipeRepositoryInterface;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/api/v1/recipes/filtered",
 *     summary="Filter and list recipes",
 *     tags={"Recipes"},
 *     @OA\Parameter(
 *         name="name",
 *         in="query",
 *         description="Search by recipe name",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="cuisine_type",
 *         in="query",
 *         description="Filter by cuisine type (e.g., Italian, Asian)",
 *         required=false,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Filtered recipe list",
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
 *     )
 * )
 */
class FilterRecipeController extends Controller
{
    public function __construct(
        private RecipeRepositoryInterface $recipeRepo,
        private ResponseService $response
    ) {}

    public function __invoke(Request $request)
    {
        $filters = [
            'name'         => $request->query('name'),
            'cuisine_type' => $request->query('cuisine_type'),
        ];
        $recipes = $this->recipeRepo->filterRecipes($filters);

        return $this->response->successPaginated(
            200,
            'Recipes fetched successfully',
            $recipes
        );
    }
}
