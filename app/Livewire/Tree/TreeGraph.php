<?php

namespace App\Livewire\Tree;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\FamilyTree;
use App\Models\Person;

#[Layout('components.layouts.app')]
class TreeGraph extends Component
{
    public FamilyTree $tree;
    public Person $rootPerson;

    public function mount(FamilyTree $tree, $person)
    {
        $this->tree = $tree;
        $this->rootPerson = Person::findOrFail($person);
        
        if ($this->tree->user_id !== auth()->id()) {
            abort(403);
        }
    }

    public function getGraphDataProperty()
    {
        return $this->buildTree($this->rootPerson);
    }

    private function buildTree($person, $depth = 0)
    {
        // Prevent infinite recursion loops
        if ($depth > 10) return null;

        $node = [
            'name' => $person->full_name,
            'id' => $person->id,
            'gender' => $person->gender?->value,
            'photo' => $person->photo_path ? asset($person->photo_path) : null,
        ];

        $children = $person->children;
        
        if ($children->count() > 0) {
            $node['children'] = [];
            foreach ($children as $child) {
                $childNode = $this->buildTree($child, $depth + 1);
                if ($childNode) {
                    $node['children'][] = $childNode;
                }
            }
        }

        return $node;
    }

    public function render()
    {
        return view('livewire.tree.tree-graph');
    }
}
