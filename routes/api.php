<?php

use App\Http\Controllers\Auth\LoginUserController;
use App\Http\Controllers\Auth\RegisterUserController;
use App\Http\Controllers\Recipe\DestroyRecipeController;
use App\Http\Controllers\Recipe\FilterRecipeController;
use App\Http\Controllers\Recipe\IndexRecipeController;
use App\Http\Controllers\Recipe\ShowRecipeController;
use App\Http\Controllers\Recipe\StoreRecipeController;
use App\Http\Controllers\Recipe\UpdateRecipeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/register', RegisterUserController::class)->name('user.register');
    Route::post('/login', LoginUserController::class)->name('user.login');

    Route::prefix('recipes')->middleware('auth:sanctum')->group(function () {
        Route::get('/', IndexRecipeController::class)->name('recipe.list');
        Route::post('/', StoreRecipeController::class)->name('recipe.create');
        Route::get('/{id}', ShowRecipeController::class)->name('recipe.show');
        Route::put('/{id}', UpdateRecipeController::class)->name('recipe.update');
        Route::delete('/{id}', DestroyRecipeController::class)->name('recipe.destroy');
        Route::get('/filtered', FilterRecipeController::class)->name('recipes.filter');
    });
});
