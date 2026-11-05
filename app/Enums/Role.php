<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel
{
    case Player = 'player';
    case Writer = 'writer';
    case Reviewer = 'reviewer';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Player => 'Pemain',
            self::Writer => 'Penulis',
            self::Reviewer => 'Penyemak',
            self::Admin => 'Admin',
        };
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}
