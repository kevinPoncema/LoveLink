<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Media\MediaController;
use App\Http\Controllers\Themes\ThemeController;
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

// Public authentication routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected authentication routes
Route::group(['prefix' => 'auth', 'middleware' => 'auth:sanctum'], function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
});

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Themes routes
    Route::apiResource('themes', ThemeController::class);
    
    // Media routes
    Route::apiResource('media', MediaController::class)->only(['index', 'store', 'destroy']);
});