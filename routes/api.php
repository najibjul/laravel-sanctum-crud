<?php

use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/registration', [AuthController::class, 'registration']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('/posts', PostController::class)->middleware('auth:sanctum');
    Route::post('/logout', [AuthController::class, 'logout']);
});


