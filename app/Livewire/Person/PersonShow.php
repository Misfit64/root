<?php

namespace App\Livewire\Person;

use Livewire\Component;
use App\Models\FamilyTree;
use App\Models\Person;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class PersonShow extends Component
{
    protected $listeners = ['person-updated' => '$refresh', 'spouse-added' => 'closeAddSpouse', 'parent-added' => 'closeAddParent', 'child-added' => 'closeAddChild', 'person-edit-closed' => 'closeEditPerson'];

    public FamilyTree $tree;
    public Person $person;
    public bool $showAddSpouse = false;
    public bool $showAddParent = false;
    public bool $showAddChild = false;
    public bool $showEditPerson = false;

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

    public function openAddParent()
    {
        $this->showAddParent = true;
    }

    public function closeAddParent()
    {
        $this->showAddParent = false;
    }

    public function openAddChild()
    {
        $this->showAddChild = true;
    }

    public function closeAddChild()
    {
        $this->showAddChild = false;
    }

    public function openEditPerson()
    {
        $this->showEditPerson = true;
    }

    public function closeEditPerson()
    {
        $this->showEditPerson = false;
    }

    public function removeSpouse($spouseId, \App\Services\RelationshipRemovalService $remover)
    {
        $spouse = Person::findOrFail($spouseId);
        $remover->removeSpouse($this->person, $spouse);
        $this->dispatch('person-updated');
    }

    public function removeParent($parentId, \App\Services\RelationshipRemovalService $remover)
    {
        $parent = Person::findOrFail($parentId);
        $remover->removeParent($this->person, $parent);
        $this->dispatch('person-updated');
    }

    public function removeChild($childId, \App\Services\RelationshipRemovalService $remover)
    {
        $child = Person::findOrFail($childId);
        $remover->removeChild($this->person, $child);
        $this->dispatch('person-updated');
    }
}
