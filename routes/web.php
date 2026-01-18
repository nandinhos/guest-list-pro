<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Welcome::class)->name('home');
Route::get('/docs/design-system', \App\Livewire\DesignSystemDocs::class)->name('docs.design-system');
