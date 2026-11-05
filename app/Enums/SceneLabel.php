<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum SceneLabel: string implements HasLabel, HasColor
{
    case Sahih = 'sahih';
    case Riwayat = 'riwayat';
    case Rekaan = 'rekaan';

    public function label(): string
    {
        return match ($this) {
            self::Sahih => '🟢 Sahih (hadis sahih / Al-Quran)',
            self::Riwayat => '🟡 Riwayat sirah',
            self::Rekaan => '⚪ Rekaan (watak/suasana sahaja)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Sahih => 'success',
            self::Riwayat => 'warning',
            self::Rekaan => 'gray',
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

    public function getColor(): string
    {
        return $this->color();
    }
}
