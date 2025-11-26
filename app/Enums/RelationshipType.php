<?php

namespace App\Enums;

enum RelationshipType: int
{
    case Parent = 1;
    case Child = 2;
    case Spouse = 3;
}
