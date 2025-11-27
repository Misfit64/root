<?php

use App\Models\Person;
use App\Livewire\Tree\TreeGraph;
use App\Models\FamilyTree;

// Find a person with parents and grandparents
$person = Person::has('parents.parents')->first();

if (!$person) {
    echo "No person with grandparents found.\n";
    exit;
}

echo "Person: " . $person->full_name . " (ID: " . $person->id . ")\n";

$component = new TreeGraph();
$component->tree = $person->familyTree;
$component->rootPerson = $person;

$data = $component->getGraphDataProperty();

echo "Ancestors:\n";
print_r($data['ancestors']);

// Check for duplicates in parents
$parents = $person->parents;
echo "\nDirect Parents count: " . $parents->count() . "\n";
foreach ($parents as $p) {
    echo "- " . $p->full_name . " (ID: " . $p->id . ")\n";
}

// Check siblings
$siblings = $person->siblings();
echo "\nSiblings count: " . $siblings->count() . "\n";
