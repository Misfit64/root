<?php

namespace App\Enums;

enum RelationshipSubType: int
{
    case Biological = 1;
    case Adoptive = 2;
    case Step = 3;
    case Unknown = 4;
    case Married = 5;
    case Separated = 6;
}
