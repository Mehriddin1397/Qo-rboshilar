<?php

namespace Database\Factories;

use App\Enums\UzgolonStatus;
use App\Models\Uzgolon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * DIQQAT: faqat DEMO/TEST ma'lumot — real tarixiy qo'zg'olon emas.
 *
 * @extends Factory<Uzgolon>
 */
class UzgolonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = "[DEMO DATA] ".fake()->city()." qo'zg'oloni";
        $startYear = fake()->numberBetween(1900, 1920);

        return [
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'name' => $name,
            'start_year' => $startYear,
            'end_year' => $startYear + fake()->numberBetween(1, 5),
            'short_description' => '[DEMO DATA] '.fake()->sentence(15),
            'historical_context' => '[DEMO DATA] '.fake()->paragraph(),
            'historical_location' => '[DEMO DATA] '.fake()->city(),
            'modern_location' => '[DEMO DATA] '.fake()->city(),
            'status' => UzgolonStatus::Draft,
            'featured' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => UzgolonStatus::Published]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }
}
