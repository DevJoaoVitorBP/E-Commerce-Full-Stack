<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CartController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductController;
use Illuminate\Support\Facades\Route;

// API v1 routas
Route::prefix('v1')->middleware('rate_limit')->group(function () {
    // Rotas de autenticação
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Rotas públicas
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);
    Route::get('/categories/{id}/products', [CategoryController::class, 'products']);

    // Rotas protegidas
    Route::middleware('auth:sanctum')->group(function () {
        // Rotas de autenticação
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Produtos (apenas admin)
        Route::middleware('admin')->group(function () {
            Route::post('/products', [ProductController::class, 'store']);
            Route::post('/products/{id}', [ProductController::class, 'update']); // FormData via POST com _method: PUT
            Route::put('/products/{id}', [ProductController::class, 'update']);
            Route::delete('/products/{id}', [ProductController::class, 'destroy']);
            Route::post('/products/{id}/image', [ProductController::class, 'uploadImage']);
            Route::delete('/products/{id}/image', [ProductController::class, 'deleteImage']);

            // Categorias (apenas admin)
            Route::post('/categories', [CategoryController::class, 'store']);
            Route::put('/categories/{id}', [CategoryController::class, 'update']);
            Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
        });

        // Carrinho
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart/items', [CartController::class, 'addItem']);
        Route::put('/cart/items/{itemId}', [CartController::class, 'updateItem']);
        Route::delete('/cart/items/{itemId}', [CartController::class, 'removeItem']);
        Route::delete('/cart', [CartController::class, 'clear']);

        // Pedidos
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
    });
});
