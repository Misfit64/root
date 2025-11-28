<?php

namespace App\Livewire\Person\Merge;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\FamilyTree;
use App\Models\Person;

#[Layout('components.layouts.tree')]
class MergePersonSelect extends Component
{
    public FamilyTree $tree;
    public Person $person; // The person we started with (Target)
    
    public $search = '';
    public $selectedPersonId = null;

    public function mount(FamilyTree $tree, Person $person)
    {
        $this->tree = $tree;
        $this->person = $person;
    }

    public function selectPerson($id)
    {
        $this->selectedPersonId = $id;
    }

    public function next()
    {
        if ($this->selectedPersonId) {
            return redirect()->route('person.merge.review', [
                'tree' => $this->tree->id,
                'target' => $this->person->id,
                'source' => $this->selectedPersonId,
            ]);
        }
    }

    public function render()
    {
        $results = [];
        if (strlen($this->search) > 1) {
            $results = Person::where('family_tree_id', $this->tree->id)
                ->where('id', '!=', $this->person->id) // Don't show self
                ->where(function ($query) {
                    $query->where('first_name', 'like', '%' . $this->search . '%')
                          ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })
                ->limit(10)
                ->get();
        }

        return view('livewire.person.merge.merge-person-select', [
            'results' => $results,
        ]);
    }
}
