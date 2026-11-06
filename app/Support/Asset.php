<?php

namespace App\Support;

/**
 * URL aset dengan versi ikut masa fail diubah (?v=...), supaya pelayar
 * sentiasa memuat CSS/JS terbaru selepas `git pull` — tiada lagi cache lama.
 */
class Asset
{
    public static function url(string $path): string
    {
        $file = rtrim(dirname(__DIR__, 2), '/').'/public/'.ltrim($path, '/');
        $version = is_file($file) ? filemtime($file) : null;

        return asset($path).($version ? '?v='.$version : '');
    }
}
