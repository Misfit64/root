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

    // Delete Confirmation State
    public bool $showDeleteConfirmation = false;
    public ?int $deleteTargetId = null;
    public ?string $deleteType = null; // 'spouse', 'parent', 'child'

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

    public function confirmRemoveSpouse($spouseId)
    {
        $this->deleteTargetId = $spouseId;
        $this->deleteType = 'spouse';
        $this->showDeleteConfirmation = true;
    }

    public function confirmRemoveParent($parentId)
    {
        $this->deleteTargetId = $parentId;
        $this->deleteType = 'parent';
        $this->showDeleteConfirmation = true;
    }

    public function confirmRemoveChild($childId)
    {
        $this->deleteTargetId = $childId;
        $this->deleteType = 'child';
        $this->showDeleteConfirmation = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteConfirmation = false;
        $this->deleteTargetId = null;
        $this->deleteType = null;
    }

    public function executeDelete(\App\Services\RelationshipRemovalService $remover)
    {
        if (!$this->deleteTargetId || !$this->deleteType) return;

        $target = Person::findOrFail($this->deleteTargetId);

        match ($this->deleteType) {
            'spouse' => $remover->removeSpouse($this->person, $target),
            'parent' => $remover->removeParent($this->person, $target),
            'child' => $remover->removeChild($this->person, $target),
        };

        $this->dispatch('person-updated');
        $this->cancelDelete();
    }

    // Deprecated direct remove methods (kept for safety if needed, but unused by view)
    public function removeSpouse($spouseId, \App\Services\RelationshipRemovalService $remover)
    {
        $this->confirmRemoveSpouse($spouseId);
    }

    public function removeParent($parentId, \App\Services\RelationshipRemovalService $remover)
    {
        $this->confirmRemoveParent($parentId);
    }

    public function removeChild($childId, \App\Services\RelationshipRemovalService $remover)
    {
        $this->confirmRemoveChild($childId);
    }
}
