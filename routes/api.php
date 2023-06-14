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
            Route::post("verifyOtp", [\App\Http\Controllers\Api\AuthController::class, 'verifyOtp']);
            Route::post("login", [\App\Http\Controllers\Api\AuthController::class, 'login']);
            Route::get("home", [\App\Http\Controllers\Api\HomeController::class, 'index']);
            Route::get("search", [\App\Http\Controllers\Api\HomeController::class, 'search']);
            Route::get("getResturantByCategory", [\App\Http\Controllers\Api\HomeController::class, 'getResturantByCategory']);
            Route::get("getStoresByCategory", [\App\Http\Controllers\Api\HomeController::class, 'getStoresByCategory']);
            Route::get("getMerchantDetail", [\App\Http\Controllers\Api\HomeController::class, 'getMerchantDetail']);
            Route::get("getProductSubItems", [\App\Http\Controllers\Api\HomeController::class, 'getProductSubItems']);
            Route::get("getCartItems", [\App\Http\Controllers\Api\HomeController::class, 'getCartItems'])->middleware('auth:sanctum');
            Route::post("addCart", [\App\Http\Controllers\Api\HomeController::class, 'addCart'])->middleware('auth:sanctum');
            Route::post("support", [\App\Http\Controllers\Api\HomeController::class, 'addSupport'])->middleware('auth:sanctum');
            Route::post("delete/account", [\App\Http\Controllers\Api\HomeController::class, 'deleteAcount'])->middleware('auth:sanctum');
            Route::post("add/address", [\App\Http\Controllers\Api\HomeController::class, 'addAddess'])->middleware('auth:sanctum');
            Route::get("getAddress", [\App\Http\Controllers\Api\HomeController::class, 'getAddress'])->middleware('auth:sanctum');
            Route::post("delete/address", [\App\Http\Controllers\Api\HomeController::class, 'deleteAddress'])->middleware('auth:sanctum');
            Route::get("getFavMerchant", [\App\Http\Controllers\Api\HomeController::class, 'getFavMerchants'])->middleware('auth:sanctum');
            Route::PUT("updateProfile", [\App\Http\Controllers\Api\HomeController::class, 'updateProfile'])->middleware('auth:sanctum');
            Route::get("foodDelivery", [\App\Http\Controllers\Api\HomeController::class, 'foodDelivery']);
            Route::get("stores", [\App\Http\Controllers\Api\HomeController::class, 'stores']);
            Route::get("discounts", [\App\Http\Controllers\Api\HomeController::class, 'discounts']);
            Route::post("logout", [\App\Http\Controllers\AuthController::class, 'logout'])->middleware('auth:sanctum');
        });
    });
});
