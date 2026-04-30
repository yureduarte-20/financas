<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\VerifyLoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentFileController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
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
        Route::get('/email', [VerifyEmailController::class, 'show'])->name('email');
        Route::post('/email', [VerifyEmailController::class, 'verify'])->name('email.post');
        Route::post('/email/resend', [VerifyEmailController::class, 'resend'])->name('email.resend');

        Route::get('/login', [VerifyLoginController::class, 'show'])->name('login');
        Route::post('/login', [VerifyLoginController::class, 'verify'])->name('login.post');
        Route::post('/login/resend', [VerifyLoginController::class, 'resend'])->name('login.resend');
    });

    Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::view('/expenses', 'expenses.index')->name('expenses.index');
    Route::view('/incomes', 'incomes.index')->name('incomes.index');
    Route::view('/import-document', 'import-document')->name('documents.import');
    Route::get('/documents/{document}/file', [DocumentFileController::class, 'show'])->name('documents.file');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/invoice/pdf', [InvoiceController::class, 'generate'])->name('invoice.pdf');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/style-guide', fn() => view('style-guide'))->name('style-guide');
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
Route::post('/{token}/webhook', [\App\Http\Controllers\TelegramWebhookController::class, 'handle']);