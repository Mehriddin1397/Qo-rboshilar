<?php

namespace Database\Factories;

use App\Enums\HistoricalAccuracyStatus;
use App\Enums\HistoricalRegionStatus;
use App\Enums\HistoricalRegionType;
use App\Models\HistoricalRegion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * DIQQAT: faqat DEMO/TEST ma'lumot. Default geojson — bo'sh FeatureCollection
 * (hech qanday real tarixiy chegara emas, Faza 12 §45 talabiga mos).
 *
 * @extends Factory<HistoricalRegion>
 */
class HistoricalRegionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = '[DEMO] '.fake()->unique()->city().' tarixiy hududi (TEST DATA)';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'historical_name' => null,
            'modern_name' => null,
            'region_type' => HistoricalRegionType::Other,
            'description' => '[DEMO DATA] '.fake()->sentence(12),
            'geojson' => ['type' => 'FeatureCollection', 'features' => []],
            'status' => HistoricalRegionStatus::Draft,
            'accuracy_status' => HistoricalAccuracyStatus::Uncertain,
            'featured' => false,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => HistoricalRegionStatus::Published]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }

    public function verified(): static
    {
        return $this->state(fn () => ['accuracy_status' => HistoricalAccuracyStatus::Verified]);
    }
}
