<?php

namespace App\Services;

/**
 * Perbandingan ringkas baris-demi-baris untuk paparan penyemak.
 * Mengembalikan senarai ['type' => 'same'|'added'|'removed', 'text' => ...].
 */
class TextDiff
{
    public static function lines(?string $old, ?string $new): array
    {
        $a = self::split($old);
        $b = self::split($new);

        // LCS (cukup untuk teks babak yang pendek)
        $n = count($a);
        $m = count($b);
        $lcs = array_fill(0, $n + 1, array_fill(0, $m + 1, 0));

        for ($i = $n - 1; $i >= 0; $i--) {
            for ($j = $m - 1; $j >= 0; $j--) {
                $lcs[$i][$j] = $a[$i] === $b[$j]
                    ? $lcs[$i + 1][$j + 1] + 1
                    : max($lcs[$i + 1][$j], $lcs[$i][$j + 1]);
            }
        }

        $out = [];
        $i = $j = 0;
        while ($i < $n && $j < $m) {
            if ($a[$i] === $b[$j]) {
                $out[] = ['type' => 'same', 'text' => $a[$i]];
                $i++;
                $j++;
            } elseif ($lcs[$i + 1][$j] >= $lcs[$i][$j + 1]) {
                $out[] = ['type' => 'removed', 'text' => $a[$i++]];
            } else {
                $out[] = ['type' => 'added', 'text' => $b[$j++]];
            }
        }
        while ($i < $n) {
            $out[] = ['type' => 'removed', 'text' => $a[$i++]];
        }
        while ($j < $m) {
            $out[] = ['type' => 'added', 'text' => $b[$j++]];
        }

        return $out;
    }

    private static function split(?string $html): array
    {
        if ($html === null || trim($html) === '') {
            return [];
        }

        $text = preg_replace('/<(br|\/p|\/li|\/h[1-6])\s*\/?>/i', "\n", $html);
        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return array_values(array_filter(array_map('trim', explode("\n", $text)), fn ($l) => $l !== ''));
    }
}
