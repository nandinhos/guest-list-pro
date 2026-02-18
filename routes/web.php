<?php

use App\Http\Controllers\Auth\LogoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Landing\Index::class)->name('home');
Route::get('/docs/design-system', \App\Livewire\DesignSystemDocs::class)->name('docs.design-system');

// Autenticação unificada
Route::middleware('guest')->group(function () {
    Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
});

Route::post('/logout', LogoutController::class)->name('logout')->middleware('auth');
