<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Faza 12 §10-11: bu qoida faqat TEXNIK GeoJSON to'g'riligini tekshiradi (valid JSON,
 * 'type' mavjud, tuzilishi GeoJSON spec'iga mos) — geometriyaning TARIXIY jihatdan
 * to'g'riligini (haqiqiy chegara, sana, joy) tasdiqlamaydi. Tarixiy tekshiruv Faza 13'da,
 * manba asosida, alohida jarayon sifatida amalga oshiriladi.
 *
 * Qamrov ataylab tor: HistoricalRegion/HistoricalMapLayer hozircha faqat
 * Feature/FeatureCollection/Polygon/MultiPolygon geometriyalarini saqlaydi (§10).
 *
 * Faza 13 §34-35: koordinatalar WGS84/EPSG:4326 (longitude -180..180, latitude
 * -90..90) diapazonida bo'lishi endi ham tekshiriladi — MapLibre shu formatni
 * kutadi, boshqa CRS'dagi qiymat xaritada butunlay noto'g'ri joyga tushib qolardi.
 */
class ValidGeoJson implements ValidationRule
{
    private const ALLOWED_TOP_LEVEL_TYPES = ['Feature', 'FeatureCollection', 'Polygon', 'MultiPolygon'];

    private const ALLOWED_GEOMETRY_TYPES = ['Point', 'MultiPoint', 'LineString', 'MultiLineString', 'Polygon', 'MultiPolygon'];

    /**
     * Faza 12'da 100KB edi — real, batafsil tarixiy chegara (ko'p vertex'li
     * Polygon/MultiPolygon) osongina bir necha yuz KB bo'lishi mumkin. Faza 13
     * §12 talabiga ko'ra 5MB'gacha kengaytirildi (baribir cheksiz emas — DoS/
     * xato-yuklangan fayldan himoya sifatida yuqori chegara saqlanadi).
     */
    private const MAX_BYTES = 5 * 1024 * 1024;

    /**
     * §9/§40: bo'sh placeholder ("features": []) haqiqiy geometriya CLAIM qilmaydi —
     * shuning uchun "manba talab qilinadimi" tekshiruvi shu funksiyaga tayanadi,
     * shunchaki `filled($geojson)`ga emas (bo'sh FeatureCollection ham "filled" satr,
     * lekin hech qanday tarixiy da'vo bermaydi).
     */
    public static function hasRealGeometry(?string $value): bool
    {
        if (! filled($value)) {
            return false;
        }

        $decoded = json_decode($value, true);

        if (! is_array($decoded)) {
            return false;
        }

        return match ($decoded['type'] ?? null) {
            'FeatureCollection' => ! empty($decoded['features']),
            'Feature' => ! empty($decoded['geometry']),
            'Polygon', 'MultiPolygon' => ! empty($decoded['coordinates']),
            default => false,
        };
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            $fail('Bu maydon JSON matn sifatida yuborilishi kerak.');

            return;
        }

        if (strlen($value) > self::MAX_BYTES) {
            $fail('GeoJSON hajmi juda katta (maksimal 5MB).');

            return;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            $fail('Bu maydon valid JSON emas: '.json_last_error_msg());

            return;
        }

        if (! isset($decoded['type']) || ! is_string($decoded['type'])) {
            $fail("GeoJSON 'type' maydoniga ega bo'lishi kerak.");

            return;
        }

        if (! in_array($decoded['type'], self::ALLOWED_TOP_LEVEL_TYPES, true)) {
            $fail("GeoJSON turi quyidagilardan biri bo'lishi kerak: ".implode(', ', self::ALLOWED_TOP_LEVEL_TYPES).'.');

            return;
        }

        $valid = match ($decoded['type']) {
            'FeatureCollection' => $this->isValidFeatureCollection($decoded),
            'Feature' => $this->isValidFeature($decoded),
            'Polygon', 'MultiPolygon' => $this->isValidGeometry($decoded),
            default => false,
        };

        if (! $valid) {
            $fail("GeoJSON tuzilishi noto'g'ri (kerakli maydonlar yo'q yoki noto'g'ri formatda).");
        }
    }

    private function isValidFeatureCollection(array $data): bool
    {
        if (! isset($data['features']) || ! is_array($data['features'])) {
            return false;
        }

        foreach ($data['features'] as $feature) {
            if (! is_array($feature) || ! $this->isValidFeature($feature)) {
                return false;
            }
        }

        return true;
    }

    private function isValidFeature(array $data): bool
    {
        if (($data['type'] ?? null) !== 'Feature' || ! array_key_exists('geometry', $data)) {
            return false;
        }

        if ($data['geometry'] === null) {
            return true;
        }

        return is_array($data['geometry']) && $this->isValidGeometry($data['geometry']);
    }

    private function isValidGeometry(array $geometry): bool
    {
        if (! isset($geometry['type'], $geometry['coordinates'])) {
            return false;
        }

        return in_array($geometry['type'], self::ALLOWED_GEOMETRY_TYPES, true)
            && is_array($geometry['coordinates'])
            && $this->coordinatesInRange($geometry['coordinates']);
    }

    /**
     * Point/LineString/Polygon/MultiPolygon va h.k. har xil chuqurlikda ichma-ich
     * massiv bo'ladi — bu funksiya rekursiv ravishda eng ichki [lon, lat] (yoki
     * [lon, lat, elevation]) juftlarigacha tushadi va WGS84 diapazonini tekshiradi.
     *
     * @param  array<int, mixed>  $coordinates
     */
    private function coordinatesInRange(array $coordinates): bool
    {
        if ($this->isCoordinatePair($coordinates)) {
            [$longitude, $latitude] = $coordinates;

            return $longitude >= -180 && $longitude <= 180 && $latitude >= -90 && $latitude <= 90;
        }

        foreach ($coordinates as $item) {
            if (! is_array($item) || ! $this->coordinatesInRange($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  array<int, mixed>  $value
     */
    private function isCoordinatePair(array $value): bool
    {
        if (! array_is_list($value) || count($value) < 2 || count($value) > 3) {
            return false;
        }

        foreach ($value as $component) {
            if (! is_int($component) && ! is_float($component)) {
                return false;
            }
        }

        return true;
    }
}
