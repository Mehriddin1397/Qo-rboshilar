<?php

namespace Database\Factories;

use App\Models\Period;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * DIQQAT: faqat DEMO/TEST ma'lumot — real tarixiy davr emas.
 *
 * @extends Factory<Period>
 */
class PeriodFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->unique()->numberBetween(1850, 1920);
        $name = "[DEMO] {$start}-yillar davri";

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'start_year' => $start,
            'end_year' => $start + fake()->numberBetween(1, 10),
            'description' => '[DEMO DATA] '.fake()->sentence(),
        ];
    }
}
