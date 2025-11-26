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
    Route::get('/trees', App\Livewire\Tree\TreeIndex::class)->name('tree.index');
    Route::get('/trees/{tree}', App\Livewire\Tree\TreeShow::class)->name('tree.show');
    Route::get('/trees/{tree}/person/{person}', PersonShow::class)->name('person.show');
    Route::get('/trees/{tree}/person/{person}/merge', App\Livewire\Person\Merge\MergePersonSelect::class)->name('person.merge.select');
    Route::get('/trees/{tree}/person/{target}/merge/{source}', App\Livewire\Person\Merge\MergePersonReview::class)->name('person.merge.review');
    Route::get('/trees/{tree}/graph/{person}', App\Livewire\Tree\TreeGraph::class)->name('tree.graph');
});

require __DIR__.'/auth.php';

