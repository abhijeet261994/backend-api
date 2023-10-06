<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\WishlistController;
use App\Http\Controllers\API\CartController;

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

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    
    Route::group(['prefix' => "order"], function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::post('/store', [OrderController::class, 'store']);
        Route::get('/show/{order}', [OrderController::class, 'show']);
        Route::post('/update/{order}', [OrderController::class, 'update']);
        Route::delete('/destroy/{order}', [OrderController::class, 'destroy']);
    });
    
    Route::group(['prefix' => "wishlist"], function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/store', [WishlistController::class, 'store']);
        Route::get('/show/{wishlist}', [WishlistController::class, 'show']);
        Route::post('/update/{wishlist}', [WishlistController::class, 'update']);
        Route::delete('/destroy/{wishlist}', [WishlistController::class, 'destroy']);
    });
    Route::group(['prefix' => "cart"], function () {
        Route::post('/add', [CartController::class, 'addToCart']);
        Route::post('/remove/{cart}', [CartController::class, 'removeFromCart']);
        Route::post('/checkout', [CartController::class, 'checkout']);
    });

});
