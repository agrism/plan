<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\IdeaController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TenantController;
use App\Http\Middleware\EnsureCurrentTenant;
use Illuminate\Support\Facades\Route;

// Locale Switcher Route
Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['lv', 'en'], true)) {
        session(['locale' => $locale]);
    }
    return back();
})->name('locale.switch');

// Direct Invite Route
Route::get('/join/{code}', [TenantController::class, 'joinByCode'])->name('tenants.join_code');

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/login/as/{user}', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Password Reset Routes (Guests)
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

// Email Verification Routes
Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed'])
    ->name('verification.verify');
Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

// Protected Routes
Route::middleware(['auth', EnsureCurrentTenant::class])->group(function () {
    Route::get('/', [IdeaController::class, 'index'])->name('ideas.index');
    Route::post('/ideas', [IdeaController::class, 'store'])->name('ideas.store');
    Route::get('/ideas/{task}/edit', [IdeaController::class, 'edit'])->name('ideas.edit');
    Route::post('/ideas/{task}/update', [IdeaController::class, 'update'])->name('ideas.update');
    Route::match(['put', 'patch'], '/ideas/{task}', [IdeaController::class, 'update'])->name('ideas.patch_update');
    Route::patch('/ideas/{task}/schedule', [IdeaController::class, 'schedule'])->name('ideas.schedule');
    Route::patch('/ideas/{task}/toggle', [IdeaController::class, 'toggle'])->name('ideas.toggle');
    Route::post('/ideas/{task}/react', [IdeaController::class, 'react'])->name('ideas.react');
    Route::delete('/ideas/{task}', [IdeaController::class, 'destroy'])->name('ideas.destroy');

    // Task Comments
    Route::post('/ideas/{task}/comments', [TaskCommentController::class, 'store'])->name('ideas.comments.store');
    Route::delete('/ideas/{task}/comments/{comment}', [TaskCommentController::class, 'destroy'])->name('ideas.comments.destroy');

    // Categories
    Route::post('/categories', [\App\Http\Controllers\CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{category}/edit', [\App\Http\Controllers\CategoryController::class, 'edit'])->name('categories.edit');
    Route::post('/categories/{category}/update', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.update');
    Route::match(['put', 'patch'], '/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'update'])->name('categories.patch_update');
    Route::delete('/categories/{category}', [\App\Http\Controllers\CategoryController::class, 'destroy'])->name('categories.destroy');

    // Tenant Switch, Create & Settings
    Route::get('/tenants/settings', [TenantController::class, 'settings'])->name('tenants.settings');
    Route::post('/tenants/switch/{id}', [TenantController::class, 'switchTenant'])->name('tenants.switch');
    Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::post('/tenants/{tenant}/update', [TenantController::class, 'update'])->name('tenants.update');
    Route::match(['put', 'patch'], '/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.patch_update');
    Route::post('/tenants/join', [TenantController::class, 'join'])->name('tenants.join');
    Route::post('/tenants/{tenant}/leave', [TenantController::class, 'leave'])->name('tenants.leave');
    
    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
