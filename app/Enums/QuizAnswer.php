<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum QuizAnswer: string implements HasLabel
{
    case Fact = 'fakta';
    case Myth = 'mitos';
    case Weak = 'lemah';

    public function label(): string
    {
        return match ($this) {
            self::Fact => 'Fakta (riwayat sahih)',
            self::Myth => 'Mitos (tiada asas)',
            self::Weak => 'Riwayat lemah / dipertikaikan',
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
