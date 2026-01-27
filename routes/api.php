<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Landing\LandingController;
use App\Http\Controllers\Landing\LandingMediaController;
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

// Public routes for landing pages (no authentication required)
Route::group(['prefix' => 'public'], function () {
    Route::get('/landing/{slug}', [LandingController::class, 'show']);
});

// Public landing routes (direct access by ID or slug)
Route::get('/landings/{identifier}', [LandingController::class, 'show']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Themes routes
    Route::apiResource('themes', ThemeController::class);
    
    // Media routes
    Route::apiResource('media', MediaController::class)->only(['index', 'store', 'destroy']);
    
    // Landing routes
    Route::apiResource('landings', LandingController::class)->except(['show']);
    
    // Landing Media routes
    Route::group(['prefix' => 'landings/{landing}'], function () {
        Route::post('/media', [LandingMediaController::class, 'store']);
        Route::delete('/media/{media}', [LandingMediaController::class, 'destroy']);
        Route::put('/media/reorder', [LandingMediaController::class, 'reorder']);
    });
});