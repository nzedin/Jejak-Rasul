<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum SourceType: string implements HasLabel
{
    case Quran = 'quran';
    case Hadith = 'hadis';
    case SirahBook = 'kitab_sirah';

    public function label(): string
    {
        return match ($this) {
            self::Quran => 'Al-Quran',
            self::Hadith => 'Hadis',
            self::SirahBook => 'Kitab sirah',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $s) => [$s->value => $s->label()])->all();
    }

    public function getLabel(): string
    {
        return $this->label();
    }
}
