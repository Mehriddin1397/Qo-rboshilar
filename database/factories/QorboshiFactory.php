<?php

namespace Database\Factories;

use App\Enums\QorboshiStatus;
use App\Models\Qorboshi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * DIQQAT: Bu factory faqat DEMO/TEST ma'lumot generatsiya qiladi (development va
 * avtomatlashtirilgan test uchun). Hech qanday real tarixiy fakt ishlatilmaydi —
 * barcha maydonlar aniq "[DEMO DATA]" prefiksi bilan belgilanadi.
 *
 * @extends Factory<Qorboshi>
 */
class QorboshiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = '[DEMO DATA] '.fake()->firstName().' '.fake()->lastName();

        return [
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'full_name' => $name,
            'short_description' => '[DEMO DATA] '.fake()->sentence(15),
            'biography' => "[DEMO DATA] Bu — test uchun avtomatik yaratilgan biografiya matni.\n\n".fake()->paragraphs(3, true),
            'historical_context' => '[DEMO DATA] '.fake()->paragraph(),
            'birth_year' => fake()->numberBetween(1850, 1895),
            'birth_place' => '[DEMO DATA] '.fake()->city(),
            'death_year' => fake()->numberBetween(1900, 1945),
            'death_place' => '[DEMO DATA] '.fake()->city(),
            'active_from_year' => fake()->numberBetween(1900, 1915),
            'active_to_year' => fake()->numberBetween(1916, 1930),
            'status' => QorboshiStatus::Draft,
            'featured' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => QorboshiStatus::Published]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }
}
