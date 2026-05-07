<?php

use Illuminate\Support\Facades\Route;

// ✅ Publik
Route::view('/', 'welcome');

// 🔒 Admin only
Route::middleware(['auth', 'verified', 'isAdmin'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::view('/profile', 'profile')->name('profile');
});

require __DIR__.'/auth.php';