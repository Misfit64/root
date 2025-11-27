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

    public $personToDeleteId = null;
    public $showDeleteConfirmation = false;
    
    public $editingPersonId = null;
    public $showEditModal = false;

    protected $listeners = [
        'person-added' => 'closeAddPerson',
        'person-edit-closed' => 'closeEditModal',
        'person-updated' => '$refresh'
    ];

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

    // Delete Logic
    public function deletePerson($personId)
    {
        $this->personToDeleteId = $personId;
        $this->showDeleteConfirmation = true;
    }

    public function confirmDelete()
    {
        if ($this->personToDeleteId) {
            $person = Person::where('family_tree_id', $this->tree->id)->findOrFail($this->personToDeleteId);
            
            if ($person->photo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($person->photo_path);
            }
    
            $person->delete();
            $this->dispatch('person-deleted');
        }
        
        $this->cancelDelete();
    }

    public function cancelDelete()
    {
        $this->showDeleteConfirmation = false;
        $this->personToDeleteId = null;
    }

    // Edit Logic
    public function editPerson($personId)
    {
        $this->editingPersonId = $personId;
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->editingPersonId = null;
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
