<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum MediaType: string implements HasLabel
{
    case Image = 'gambar';
    case Video = 'video';
    case Link = 'pautan';

    public function getLabel(): string
    {
        return match ($this) {
            self::Image => 'Gambar',
            self::Video => 'Video',
            self::Link => 'Pautan',
        };
    }
}
