<?php

namespace App\Support;

class LegacyImage
{
    public static function first(?string $raw, string $fallback = 'logoB.png'): string
    {
        if (!$raw) {
            return $fallback;
        }

        $raw = trim($raw);
        if ($raw === '') {
            return $fallback;
        }

        if (str_starts_with($raw, '[')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded) && !empty($decoded[0]) && is_string($decoded[0])) {
                return trim($decoded[0], " \t\n\r\0\x0B\"'");
            }
        }

        if (str_contains($raw, ',')) {
            return trim((string) explode(',', $raw)[0], " \t\n\r\0\x0B\"'");
        }

        return trim($raw, " \t\n\r\0\x0B\"'");
    }
}
