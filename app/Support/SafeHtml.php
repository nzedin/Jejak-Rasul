<?php

namespace App\Support;

/**
 * Bersihkan HTML daripada RichEditor sebelum dipapar kepada pemain:
 * hanya tag format asas dibenarkan, dan SEMUA atribut dibuang
 * (tiada onclick, style, href javascript:, dsb.).
 */
class SafeHtml
{
    private const ALLOWED = '<p><br><strong><b><em><i><u><s><blockquote><ul><ol><li><h2><h3>';

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        // Buang terus kandungan tag berbahaya (bukan sekadar tagnya).
        $html = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $html);
        $html = strip_tags($html, self::ALLOWED);

        return preg_replace('/<(\/?)([a-z0-9]+)\b[^>]*>/i', '<$1$2>', $html);
    }
}
