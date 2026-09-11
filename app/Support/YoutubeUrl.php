<?php

namespace App\Support;

/**
 * YouTube URL'lardan xavfsiz video ID ajratib olish.
 *
 * XAVFSIZLIK: bu klass foydalanuvchidan kelgan URL'ni HECH QACHON to'g'ridan-to'g'ri
 * iframe `src`ga qo'ymaydi — faqat 11 belgili video ID'ni regex orqali ajratib oladi
 * (yoki null qaytaradi). Embed URL doim `Video::embedUrl()`da qattiq YouTube domenidan
 * qo'lda quriladi (`youtube-nocookie.com/embed/{id}`), shuning uchun arbitrary iframe
 * HTML yoki boshqa domenlar hech qachon render qilinmaydi.
 */
class YoutubeUrl
{
    private const ID_PATTERN = '[A-Za-z0-9_-]{11}';

    /**
     * Qo'llab-quvvatlanadigan formatlar:
     * - https://www.youtube.com/watch?v=ID
     * - https://youtu.be/ID
     * - https://www.youtube.com/shorts/ID
     * - https://www.youtube.com/embed/ID
     * - m.youtube.com, youtube-nocookie.com variantlari
     * - qo'shimcha query parametrlar (&t=, ?si=...) e'tiborga olinmaydi
     */
    public static function extractId(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $patterns = [
            '~youtube(?:-nocookie)?\.com/watch\?(?:.*&)?v=('.self::ID_PATTERN.')~i',
            '~youtu\.be/('.self::ID_PATTERN.')~i',
            '~youtube(?:-nocookie)?\.com/shorts/('.self::ID_PATTERN.')~i',
            '~youtube(?:-nocookie)?\.com/embed/('.self::ID_PATTERN.')~i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches) === 1) {
                return $matches[1];
            }
        }

        return null;
    }

    public static function isValid(?string $url): bool
    {
        return self::extractId($url) !== null;
    }

    public static function embedUrl(string $youtubeId): string
    {
        return 'https://www.youtube-nocookie.com/embed/'.rawurlencode($youtubeId);
    }

    public static function thumbnailUrl(string $youtubeId): string
    {
        return 'https://img.youtube.com/vi/'.rawurlencode($youtubeId).'/hqdefault.jpg';
    }
}
