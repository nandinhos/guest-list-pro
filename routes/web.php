<?php

use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Welcome::class)->name('home');
Route::get('/showcase', \App\Livewire\ComponentShowcase::class)->name('showcase');
