<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

/**
 * Aliran status kandungan:
 * draf → dihantar → (perlu_pindaan ↺) → diluluskan
 */
enum RevisionStatus: string implements HasLabel, HasColor
{
    case Draft = 'draf';
    case Submitted = 'dihantar';
    case ChangesRequested = 'perlu_pindaan';
    case Approved = 'diluluskan';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Submitted => 'Dihantar untuk semakan',
            self::ChangesRequested => 'Perlu pindaan',
            self::Approved => 'Diluluskan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Submitted => 'warning',
            self::ChangesRequested => 'danger',
            self::Approved => 'success',
        };
    }

    /** Status di mana penulis masih boleh mengubah revision yang sama. */
    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::ChangesRequested], true);
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
