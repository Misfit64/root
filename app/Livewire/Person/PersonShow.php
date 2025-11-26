<?php

namespace App\Livewire\Person;

use Livewire\Component;
use App\Models\FamilyTree;
use App\Models\Person;

class PersonShow extends Component
{
    protected $listeners = ['person-updated' => '$refresh'];

    public FamilyTree $tree;
    public Person $person;
    public bool $showAddSpouse = false;

    public function mount(FamilyTree $tree, Person $person)
    {
        $this->tree = $tree;
        $this->person = $person;
    }

    public function render()
    {
        return view('livewire.person.person-show');
    }

    public function getParentsProperty()
    {
        return $this->person->parents;
    }

    public function getChildrenProperty()
    {
        return $this->person->children;
    }

    public function getSpousesProperty()
    {
        return $this->person->spouses;
    }

    public function getSiblingsProperty()
    {
        return $this->person->siblings();
    }

    public function openAddSpouse()
    {
        $this->showAddSpouse = true;
    }

    public function closeAddSpouse()
    {
        $this->showAddSpouse = false;
    }
}
