<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Person\PersonShow;

Route::get('/demo', function () {
    $demoTree = \App\Models\FamilyTree::where('is_public', true)->first();
    if (!$demoTree) {
        return redirect('/');
    }
    // Find a root person to start with
    $root = \App\Models\Person::where('family_tree_id', $demoTree->id)->first();
    return redirect()->route('tree.graph', ['tree' => $demoTree->id, 'person' => $root->id]);
})->name('demo');

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('tree.index');
    }
    return view('welcome');
});

Route::get('dashboard', function () {
    return redirect()->route('tree.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/trees/{tree}/graph/{person}', App\Livewire\Tree\TreeGraph::class)->name('tree.graph');

Route::middleware('auth')->group(function () {
    Route::get('/trees', App\Livewire\Tree\TreeIndex::class)->name('tree.index');
    Route::get('/trees/{tree}', App\Livewire\Tree\TreeShow::class)->name('tree.show');
    Route::get('/trees/{tree}/person/{person}', PersonShow::class)->name('person.show');
    Route::get('/trees/{tree}/person/{person}/merge', App\Livewire\Person\Merge\MergePersonSelect::class)->name('person.merge.select');
    Route::get('/trees/{tree}/person/{target}/merge/{source}', App\Livewire\Person\Merge\MergePersonReview::class)->name('person.merge.review');
    // Route::get('/trees/{tree}/graph/{person}', App\Livewire\Tree\TreeGraph::class)->name('tree.graph'); // Moved out
});

require __DIR__.'/auth.php';

