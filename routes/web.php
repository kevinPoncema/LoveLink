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

    // Gestión de Temas
    Route::get('/themes', function () {
        return Inertia::render('Themes');
    })->name('themes.index');

    // Versión simple de themes para testing
    Route::get('/themes-simple', function () {
        return Inertia::render('Dashboard', [
            'test_message' => 'Esta es una versión simple de themes. Si ves esto, la autenticación funciona.'
        ]);
    })->name('themes.simple');

    // Gestión de Media
    Route::get('/media', function () {
        return Inertia::render('Media');
    })->name('media.index');

    // Ruta de test para debugging
    Route::get('/test-auth', function () {
        return Inertia::render('AuthTest', [
            'test_data' => [
                'user' => auth()->user(),
                'is_authenticated' => auth()->check(),
                'email_verified' => auth()->user()?->email_verified_at,
                'guard' => auth()->getDefaultDriver(),
            ]
        ]);
    })->name('test.auth');

    // TODO: Añadir más rutas protegidas aquí cuando se implementen las páginas
    // Route::get('/landings', [LandingController::class, 'index'])->name('landings.index');
    // Route::get('/invitations', [InvitationController::class, 'index'])->name('invitations.index');
});

// Ruta de test para debugging (sin autenticación requerida)
Route::get('/test-auth-public', function () {
    return Inertia::render('AuthTest', [
        'test_data' => [
            'user' => auth()->user(),
            'is_authenticated' => auth()->check(),
            'email_verified' => auth()->user()?->email_verified_at,
            'guard' => auth()->getDefaultDriver(),
        ]
    ]);
})->name('test.auth.public');

require __DIR__.'/settings.php';
