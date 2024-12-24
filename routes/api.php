<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\v1\AuthController;
use App\Http\Controllers\API\v1\ShoppingCartController;
use App\Http\Controllers\API\v1\ProductController;
use App\Http\Controllers\API\v1\OrderController;

Route::prefix('v1')->group(function(){
    //User Register Route
    Route::post('/register', [AuthController::class,'register']);
    Route::post('/login', [AuthController::class,'login']);

    // These two must be protected, since there must be a register or login first
    Route::middleware('auth:sanctum')->get('/profile', [AuthController::class,'profile']);
    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class,'logout']);

    //Shopping Cart Route
    Route::prefix('cart')->middleware('auth:sanctum')->group(function(){
        Route::get('/',[ShoppingCartController::class,'show']);
        Route::post('/add',[ShoppingCartController::class,'store']);
        Route::put('/update/{CartItemID}',[ShoppingCartController::class,'update']);
        Route::delete('/delete/{CartItemID}',[ShoppingCartController::class,'destroy']);
    });

    //Product route
    Route::prefix('products')->group(function(){
        Route::get('/',[ProductController::class,'index']);
        Route::get('/search',[ProductController::class,'search']);
        Route::get('/{id}',[ProductController::class,'show']);
        Route::post('/',[ProductController::class,'store']);
        Route::put('/{id}',[ProductController::class,'update']);
        Route::delete('/{id}',[ProductController::class,'destroy']);
    });

    //Orders Route
    Route::prefix('orders')->middleware('auth:sanctum')->group(function(){
        Route::get('/',[OrderController::class,'index']);
        Route::get('/{id}',[OrderController::class,'show']);
        Route::post('/create',[OrderController::class,'store']);
    });

});

