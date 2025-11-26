<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Person\PersonShow;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])  
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware('auth')->group(function () {
    Route::get('/trees/{tree}/person/{person}', PersonShow::class)->name('person.show');
});

require __DIR__.'/auth.php';
