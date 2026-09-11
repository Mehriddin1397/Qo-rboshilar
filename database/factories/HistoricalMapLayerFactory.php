<?php

namespace Database\Factories;

use App\Enums\HistoricalAccuracyStatus;
use App\Enums\HistoricalMapLayerStatus;
use App\Models\HistoricalMapLayer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * DIQQAT: faqat DEMO/TEST ma'lumot. Default geojson — bo'sh FeatureCollection.
 *
 * @extends Factory<HistoricalMapLayer>
 */
class HistoricalMapLayerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = '[DEMO] '.fake()->unique()->words(3, true).' qatlami (TEST DATA)';

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => '[DEMO DATA] '.fake()->sentence(10),
            'image_path' => null,
            'opacity' => 0.7,
            'bounds' => null,
            'geojson' => ['type' => 'FeatureCollection', 'features' => []],
            'is_active' => false,
            'status' => HistoricalMapLayerStatus::Draft,
            'accuracy_status' => HistoricalAccuracyStatus::Uncertain,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => HistoricalMapLayerStatus::Published]);
    }

    public function active(): static
    {
        return $this->state(fn () => ['is_active' => true]);
    }

    public function verified(): static
    {
        return $this->state(fn () => ['accuracy_status' => HistoricalAccuracyStatus::Verified]);
    }
}
