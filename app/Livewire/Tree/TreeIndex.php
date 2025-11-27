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
    public $editingTreeId = null;

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($this->editingTreeId) {
            $tree = FamilyTree::where('user_id', auth()->id())->findOrFail($this->editingTreeId);
            $tree->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            $this->dispatch('tree-updated');
            $this->dispatch('tree-saved');
        } else {
            FamilyTree::create([
                'user_id' => auth()->id(),
                'name' => $this->name,
                'description' => $this->description,
                'slug' => Str::slug($this->name) . '-' . Str::random(6),
            ]);
            $this->dispatch('tree-created');
            $this->dispatch('tree-saved');
        }

        $this->reset(['name', 'description', 'editingTreeId']);
    }

    public function edit($id)
    {
        $tree = FamilyTree::where('user_id', auth()->id())->findOrFail($id);
        $this->editingTreeId = $tree->id;
        $this->name = $tree->name;
        $this->description = $tree->description;
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
