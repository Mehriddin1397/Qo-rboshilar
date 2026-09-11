<?php

namespace Database\Factories;

use App\Enums\MapMarkerStatus;
use App\Enums\MapMarkerType;
use App\Models\MapMarker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * DIQQAT: faqat DEMO/TEST ma'lumot — real koordinata emas.
 *
 * @extends Factory<MapMarker>
 */
class MapMarkerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => '[DEMO DATA] '.fake()->city(),
            'latitude' => fake()->latitude(37, 45),
            'longitude' => fake()->longitude(56, 73),
            'type' => MapMarkerType::Uprising,
            'description' => '[DEMO DATA] '.fake()->sentence(),
            'status' => MapMarkerStatus::Published,
            // Default true: bu factory ilgari mavjud `oldestOfMany()` xatti-harakatini
            // taqlid qiladi — bitta uzgolon_id uchun yaratilgan yagona marker uning
            // "asosiy" markeri hisoblanadi (§Uzgolon::primaryMarker). Bir nechta marker
            // yaratilganda ikkinchisi uchun ->state(['is_primary' => false]) bering.
            'is_primary' => true,
            'sort_order' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => MapMarkerStatus::Draft]);
    }

    public function secondary(): static
    {
        return $this->state(fn () => ['is_primary' => false]);
    }
}
