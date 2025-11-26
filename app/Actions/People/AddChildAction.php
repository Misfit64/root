<?php

namespace App\Actions\People;

use App\Models\Person;
use App\Enums\RelationshipSubtype;

class AddChildAction
{
    public function __construct(
        protected AddParentAction $addParentAction
    ) {}

    public function handle(Person $parent, Person $child, RelationshipSubtype $subtype)
    {
        // Simply reverse the parameters:
        return $this->addParentAction->handle($child, $parent, $subtype);
    }
}
