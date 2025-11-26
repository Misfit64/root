<?php

namespace App\Enums;

enum TreeVisibility: int
{
    case Private = 1;
    case Family = 2;
    case Public = 3;
}
