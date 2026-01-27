<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Rutas de autenticación (públicas)
Route::middleware('guest')->group(function () {
    Route::get('/login', function () {
        return Inertia::render('auth/Login', [
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'canRegister' => Features::enabled(Features::registration()),
            'status' => session('status'),
        ]);
    })->name('login');

    Route::get('/register', function () {
        return Inertia::render('auth/Register', [
            'canLogin' => true,
        ]);
    })->name('register');

    Route::get('/forgot-password', function () {
        return Inertia::render('auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    })->name('password.request');

    Route::get('/reset-password/{token}', function ($token) {
        return Inertia::render('auth/ResetPassword', [
            'token' => $token,
            'email' => request('email'),
        ]);
    })->name('password.reset');
});

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Ruta para obtener usuario autenticado (para AJAX requests)
    Route::get('/user', function () {
        return response()->json(auth()->user());
    })->name('user');

    // TODO: Añadir más rutas protegidas aquí cuando se implementen las páginas
    // Route::get('/landings', [LandingController::class, 'index'])->name('landings.index');
    // Route::get('/invitations', [InvitationController::class, 'index'])->name('invitations.index');
    // Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    // Route::get('/themes', [ThemeController::class, 'index'])->name('themes.index');
});

require __DIR__.'/settings.php';
