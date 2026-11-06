<?php

namespace App\Support;

/**
 * Tukar pautan YouTube / Vimeo kepada URL embed yang selamat.
 * Pautan lain tidak dibenamkan (dipapar sebagai pautan biasa sahaja).
 */
class VideoEmbed
{
    public static function from(?string $url): ?string
    {
        if (! $url || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $allowed = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be', 'vimeo.com', 'www.vimeo.com', 'player.vimeo.com'];
        if (! in_array($host, $allowed, true)) {
            return null;
        }

        $patterns = [
            '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{11})~i' => 'youtube',
            '~vimeo\.com/(?:video/)?(\d+)~i' => 'vimeo',
        ];

        foreach ($patterns as $pattern => $provider) {
            if (preg_match($pattern, $url, $m)) {
                return $provider === 'youtube'
                    ? "https://www.youtube-nocookie.com/embed/{$m[1]}?enablejsapi=1&rel=0&playsinline=1"
                    : "https://player.vimeo.com/video/{$m[1]}?api=1";
            }
        }

        return null;
    }

    public static function isSupported(?string $url): bool
    {
        return static::from($url) !== null;
    }
}
