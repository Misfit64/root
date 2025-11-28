<?php

namespace App\Livewire\Person\Merge;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\FamilyTree;
use App\Models\Person;
use App\Actions\People\MergePeopleAction;

#[Layout('components.layouts.tree')]
class MergePersonReview extends Component
{
    public FamilyTree $tree;
    public Person $target;
    public Person $source;

    // Selected values for each field (defaults to target's value)
    public $selected = [];

    public function mount(FamilyTree $tree, $target, $source)
    {
        $this->tree = $tree;
        $this->target = Person::findOrFail($target);
        $this->source = Person::findOrFail($source);

        // Initialize selection with target values
        $this->selected = [
            'first_name' => $this->target->first_name,
            'last_name' => $this->target->last_name,
            'gender' => $this->target->gender?->value,
            'birth_date' => $this->target->birth_date?->format('Y-m-d'),
            'death_date' => $this->target->death_date?->format('Y-m-d'),
            'notes' => $this->target->notes,
        ];
    }

    public function merge(MergePeopleAction $merger)
    {
        $merger->handle($this->target, $this->source, $this->selected);

        return redirect()->route('person.show', ['tree' => $this->tree->id, 'person' => $this->target->id])
            ->with('status', 'People merged successfully.');
    }

    public function render()
    {
        return view('livewire.person.merge.merge-person-review');
    }
}
