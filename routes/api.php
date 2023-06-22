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
            Route::get("getCartById", [\App\Http\Controllers\Api\HomeController::class, 'getCartById'])->middleware('auth:sanctum');
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
            Route::post("rate/order", [\App\Http\Controllers\Api\HomeController::class, 'rateOrder'])->middleware('auth:sanctum');
            Route::post("place/order", [\App\Http\Controllers\Api\OrderController::class, 'placeOrder'])->middleware('auth:sanctum');
            Route::post("courier/place/order", [\App\Http\Controllers\Api\OrderController::class, 'courierPlaceOrder'])->middleware('auth:sanctum');
            Route::get("order/detail", [\App\Http\Controllers\Api\OrderController::class, 'orderDetail'])->middleware('auth:sanctum');
            Route::get("activeOrder", [\App\Http\Controllers\Api\OrderController::class, 'activeOrder'])->middleware('auth:sanctum');
            Route::get("orderdetail", [\App\Http\Controllers\Api\OrderController::class, 'getOrderWithDetails'])->middleware('auth:sanctum');
            Route::get("notifications", [\App\Http\Controllers\Api\HomeController::class, 'notifications'])->middleware('auth:sanctum');
            Route::get("city", [\App\Http\Controllers\Api\HomeController::class, 'getCities']);
            Route::get("packages", [\App\Http\Controllers\Api\HomeController::class, 'getAllPackages']);
            Route::post("addSubscription", [\App\Http\Controllers\Api\HomeController::class, 'addSubscription'])->middleware('auth:sanctum');
            Route::post("logout", [\App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');
        });
    });

    Route::prefix('provider')->group(function () {
        Route::middleware(ChangeLocale::class)->group(function (): void {
            Route::post("verifyOtp", [\App\Http\Controllers\Api\AuthController::class, 'verifyProviderOtp']);
            Route::post("login", [\App\Http\Controllers\Api\AuthController::class, 'deliveryProviderLogin']);
            Route::post("forgetPassword", [\App\Http\Controllers\Api\AuthController::class, 'forgetPassword']);
            Route::post("goOnline", [\App\Http\Controllers\Api\ProviderController::class, 'goOnline'])->middleware('auth:sanctum')->middleware('onlinehour');
            Route::post("goOffline", [\App\Http\Controllers\Api\ProviderController::class, 'goOffline'])->middleware('auth:sanctum');
            Route::post("acceptOrder", [\App\Http\Controllers\Api\ProviderController::class, 'acceptOrder'])->middleware('auth:sanctum');
            Route::post("discardOrder", [\App\Http\Controllers\Api\ProviderController::class, 'discardOrder'])->middleware('auth:sanctum');
            Route::post("rate/order", [\App\Http\Controllers\Api\ProviderController::class, 'rateOrder'])->middleware('auth:sanctum');
            Route::post("completeOrder", [\App\Http\Controllers\Api\ProviderController::class, 'completeOrder'])->middleware('auth:sanctum');
            Route::get("dashboard", [\App\Http\Controllers\Api\ProviderController::class, 'dashboardStats'])->middleware('auth:sanctum');
            Route::get("getDateWiseEarnings", [\App\Http\Controllers\Api\ProviderController::class, 'getDateWiseEarnings'])->middleware('auth:sanctum');
            Route::PUT("updateProfile", [\App\Http\Controllers\Api\ProviderController::class, 'updateProfile'])->middleware('auth:sanctum');
            Route::PUT("updateBankDetails", [\App\Http\Controllers\Api\ProviderController::class, 'updateBankDetails'])->middleware('auth:sanctum');
            Route::PUT("changePassword", [\App\Http\Controllers\Api\ProviderController::class, 'changePassword'])->middleware('auth:sanctum');
            Route::post("logout", [\App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');
        });
    });

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::post("save/message", [\App\Http\Controllers\ChatController::class, 'saveMessage']);
        Route::get("get/messages", [\App\Http\Controllers\ChatController::class, 'getMessages']);
    });
});
