<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Latar visual babak. Hanya landskap & objek — tiada watak Nabi ﷺ atau sahabat.
 */
enum SceneSetting: string implements HasLabel
{
    case MakkahNight = 'makkah_malam';
    case DesertNight = 'gurun_malam';
    case DesertDay = 'gurun_siang';
    case Cave = 'gua';
    case Coast = 'pesisir';
    case Oasis = 'oasis';

    public function getLabel(): string
    {
        return match ($this) {
            self::MakkahNight => 'Kota Makkah waktu malam',
            self::DesertNight => 'Padang pasir waktu malam',
            self::DesertDay => 'Padang pasir waktu siang',
            self::Cave => 'Dalam gua',
            self::Coast => 'Laluan pesisir pantai',
            self::Oasis => 'Oasis / ketibaan waktu fajar',
        };
    }
}
