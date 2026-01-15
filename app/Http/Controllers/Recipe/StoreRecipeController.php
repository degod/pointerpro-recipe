<?php

namespace App\Http\Controllers\Recipe;

use App\Http\Controllers\Controller;
use App\Http\Requests\Recipe\StoreRecipeRequest;
use App\Repositories\Recipe\RecipeRepositoryInterface;
use App\Services\ResponseService;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/api/v1/recipes",
 *     tags={"Recipes"},
 *     summary="Create a new recipe",
 *     security={{"sanctum":{}}},
 *
 *     @OA\RequestBody(
 *         required=true,
 *         description="Recipe data",
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"name","cuisine_type","ingredients","steps"},
 *                 @OA\Property(property="name", type="string", example="Spaghetti Carbonara", description="Recipe name"),
 *                 @OA\Property(property="cuisine_type", type="string", example="Italian", description="Cuisine type"),
 *                 @OA\Property(property="ingredients", type="string", example="200g pasta\n4 eggs\n100g pancetta", description="Ingredients (one per line)"),
 *                 @OA\Property(property="steps", type="string", example="1. Boil pasta\n2. Fry pancetta\n3. Mix with eggs", description="Steps (one per line)"),
 *                 @OA\Property(property="picture", type="string", format="binary", description="Recipe image (optional)"),
 *                 @OA\Property(property="visibility", type="string", description="Recipe visibility")
 *             )
 *         )
 *     ),
 *
 *     @OA\Response(
 *         response=201,
 *         description="Recipe created successfully",
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
 *             @OA\Property(property="created_at", type="string", format="date-time"),
 *             @OA\Property(property="updated_at", type="string", format="date-time")
 *         )
 *     ),
 *
 *     @OA\Response(response=401, description="Unauthenticated"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
class StoreRecipeController extends Controller
{
    public function __construct(
        private RecipeRepositoryInterface $recipeRepo,
        private ResponseService $response
    ) {}

    public function __invoke(StoreRecipeRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth('sanctum')->id();

        if ($request->hasFile('picture')) {
            $data['picture'] = $request->file('picture')->store('recipes', 'public');
        }

        $recipe = $this->recipeRepo->create($data);

        return $this->response->success(201, 'Recipe created successfully', $recipe->toArray());
    }
}
