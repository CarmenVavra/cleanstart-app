<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Dashboard;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/anmelden', [LoginController::class, 'create'])->name('login');
    Route::post('/anmelden', [LoginController::class, 'store'])->name('login.store');
    Route::get('/registrieren', [RegisterController::class, 'create'])->name('register');
    Route::post('/registrieren', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/abmelden', [LoginController::class, 'destroy'])->name('logout');
});
