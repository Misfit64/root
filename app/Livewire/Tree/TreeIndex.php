<?php

namespace App\Livewire\Tree;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\FamilyTree;
use Illuminate\Support\Str;

#[Layout('components.layouts.app')]
class TreeIndex extends Component
{
    public $name = '';
    public $description = '';

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        FamilyTree::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
            'description' => $this->description,
            'slug' => Str::slug($this->name) . '-' . Str::random(6),
        ]);

        $this->reset(['name', 'description']);
        $this->dispatch('tree-created');
    }

    public function delete($id)
    {
        $tree = FamilyTree::where('user_id', auth()->id())->findOrFail($id);
        $tree->delete();
    }

    public function render()
    {
        return view('livewire.tree.tree-index', [
            'trees' => FamilyTree::where('user_id', auth()->id())->latest()->get(),
        ]);
    }
}
