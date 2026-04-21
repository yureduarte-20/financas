<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    // Verification Routes
    Route::prefix('auth/verify')->name('auth.verify.')->group(function () {
        Route::get('/email', [\App\Http\Controllers\Auth\VerifyEmailController::class, 'show'])->name('email');
        Route::post('/email', [\App\Http\Controllers\Auth\VerifyEmailController::class, 'verify'])->name('email.post');
        Route::post('/email/resend', [\App\Http\Controllers\Auth\VerifyEmailController::class, 'resend'])->name('email.resend');

        Route::get('/login', [\App\Http\Controllers\Auth\VerifyLoginController::class, 'show'])->name('login');
        Route::post('/login', [\App\Http\Controllers\Auth\VerifyLoginController::class, 'verify'])->name('login.post');
        Route::post('/login/resend', [\App\Http\Controllers\Auth\VerifyLoginController::class, 'resend'])->name('login.resend');
    });

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', fn() => view('dashboard'))->name('profile');
    Route::get('/style-guide', fn() => view('style-guide'))->name('style-guide');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
