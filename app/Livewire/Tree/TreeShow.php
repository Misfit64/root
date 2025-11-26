<?php

namespace App\Livewire\Tree;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\FamilyTree;
use App\Models\Person;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class TreeShow extends Component
{
    use WithPagination;

    public FamilyTree $tree;
    public $search = '';
    public bool $showAddPerson = false;

    protected $listeners = ['person-added' => 'closeAddPerson'];

    public function mount(FamilyTree $tree)
    {
        $this->tree = $tree;
        
        // Ensure the user owns the tree
        if ($tree->user_id !== auth()->id()) {
            abort(403);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function openAddPerson()
    {
        $this->showAddPerson = true;
    }

    public function closeAddPerson()
    {
        $this->showAddPerson = false;
        $this->dispatch('$refresh');
    }

    public function render()
    {
        $people = Person::where('family_tree_id', $this->tree->id)
            ->where(function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('first_name')
            ->paginate(10);

        return view('livewire.tree.tree-show', [
            'people' => $people,
        ]);
    }
}
