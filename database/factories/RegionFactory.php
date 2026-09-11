<?php

namespace Database\Factories;

use App\Enums\RegionStatus;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * DIQQAT: faqat DEMO/TEST ma'lumot — real hudud emas.
 *
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = '[DEMO] '.fake()->unique()->city().' viloyati';

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => '[DEMO DATA] '.fake()->sentence(),
            'status' => RegionStatus::Published,
            'sort_order' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn () => ['status' => RegionStatus::Draft]);
    }
}
