<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('v1')->group(function () {
    Route::prefix('user')->group(function () {
        Route::middleware(ChangeLocale::class)->group(function (): void {
            Route::post("register", [\App\Http\Controllers\Api\AuthController::class, 'register']);
            Route::post("login", [\App\Http\Controllers\Api\AuthController::class, 'login']);
            Route::get("home", [\App\Http\Controllers\Api\HomeController::class, 'index']);
            Route::get("search", [\App\Http\Controllers\Api\HomeController::class, 'search']);
            Route::get("getResturantByCategory", [\App\Http\Controllers\Api\HomeController::class, 'getResturantByCategory']);
            Route::get("getStoresByCategory", [\App\Http\Controllers\Api\HomeController::class, 'getStoresByCategory']);
            Route::get("getMerchantDetail", [\App\Http\Controllers\Api\HomeController::class, 'getMerchantDetail']);
            Route::get("getProductSubItems", [\App\Http\Controllers\Api\HomeController::class, 'getProductSubItems']);
            Route::post("logout", [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
        });
    });
});
