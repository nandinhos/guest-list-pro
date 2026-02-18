<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Landing\Index::class)->name('home');
Route::get('/painel/{panel}', \App\Livewire\Welcome::class)->name('panel.redirect');
Route::get('/docs/design-system', \App\Livewire\DesignSystemDocs::class)->name('docs.design-system');
