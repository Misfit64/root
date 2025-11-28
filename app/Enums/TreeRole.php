<?php

namespace App\Enums;

enum TreeRole: string
{
    case Editor = 'editor';
    case Viewer = 'viewer';
    
    public function label(): string
    {
        return match($this) {
            self::Editor => 'Editor',
            self::Viewer => 'Viewer',
        };
    }
}
