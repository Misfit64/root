<?php

namespace App\Livewire\Person\Actions;

use Livewire\Component;
use App\Models\Person;

class PersonSearch extends Component
{
    public $tree;
    public $search = '';
    public $value = null;

    public function mount($tree, $value = null)
    {
        $this->tree = $tree;
        $this->value = $value; // required for wire:model
    }

    public function selectPerson($id)
    {
        $this->value = $id;
    }

    public function render()
    {
        $results = [];

        if (strlen($this->search) > 1) {
            $results = Person::where('family_tree_id', $this->tree->id)
                ->where(function ($query) {
                    $query->where('first_name', 'like', '%'.$this->search.'%')
                          ->orWhere('last_name', 'like', '%'.$this->search.'%');
                })
                ->orderBy('first_name')
                ->limit(10)
                ->get();
        }

        return view('livewire.person.actions.person-search', compact('results'));
    }
}