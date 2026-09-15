<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\IdeaController;
use App\Http\Controllers\TenantController;
use App\Http\Middleware\EnsureCurrentTenant;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/login/as/{user}', [AuthController::class, 'quickLogin'])->name('login.quick');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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

    // Tenant Switch & Create
    Route::post('/tenants/switch/{id}', [TenantController::class, 'switchTenant'])->name('tenants.switch');
    Route::get('/tenants/create', [TenantController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::post('/tenants/join', [TenantController::class, 'join'])->name('tenants.join');
});
