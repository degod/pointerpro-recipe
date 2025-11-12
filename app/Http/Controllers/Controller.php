<?php

namespace App\Http\Controllers;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Recipe API Docs",
 *     description="API documentation for the Recipe Application",
 *     @OA\Contact(
 *         email="support@recipeapp.test",
 *         name="Recipe App Support"
 *     )
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:9020/api/v1",
 *     description="Version 1 of the Recipe API"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Use Laravel Sanctum for API authentication"
 * )
 */
abstract class Controller
{
    //
}
