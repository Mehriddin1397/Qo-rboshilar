<?php

namespace Database\Factories;

use App\Enums\SourceType;
use App\Models\SourceReference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * DIQQAT: faqat DEMO/TEST ma'lumot — real manba emas.
 *
 * @extends Factory<SourceReference>
 */
class SourceReferenceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author' => '[DEMO] '.fake()->name(),
            'title' => '[DEMO DATA] '.fake()->sentence(4),
            'publisher' => '[DEMO] '.fake()->company(),
            'year' => fake()->numberBetween(1900, 2020),
            'url' => null,
            'page' => (string) fake()->numberBetween(1, 300),
            'note' => null,
            'source_type' => fake()->randomElement(SourceType::cases()),
        ];
    }
}
