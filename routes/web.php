<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CopingItemController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RelapseController;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

// Öffentlich (Gäste und angemeldete Nutzer)
Route::get('/impressum', [LegalController::class, 'imprint'])->name('legal.imprint');
Route::get('/datenschutz', [LegalController::class, 'privacy'])->name('legal.privacy');

Route::middleware('guest')->group(function () {
    Route::get('/anmelden', [LoginController::class, 'create'])->name('login');
    Route::post('/anmelden', [LoginController::class, 'store'])->name('login.store');
    Route::get('/registrieren', [RegisterController::class, 'create'])->name('register');
    Route::post('/registrieren', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/passwort-vergessen', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/passwort-vergessen', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');
    Route::get('/passwort-zuruecksetzen/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/passwort-zuruecksetzen', [NewPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');

    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profil/export', [ProfileController::class, 'export'])->name('profile.export');
    Route::delete('/profil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/neustart', [RelapseController::class, 'create'])->name('relapse.create');
    Route::post('/neustart', [RelapseController::class, 'store'])->name('relapse.store');

    Route::get('/notfallplan', [CopingItemController::class, 'index'])->name('coping.index');
    Route::post('/notfallplan', [CopingItemController::class, 'store'])->name('coping.store');
    Route::delete('/notfallplan/{copingItem}', [CopingItemController::class, 'destroy'])->name('coping.destroy');

    Route::post('/abmelden', [LoginController::class, 'destroy'])->name('logout');
});
