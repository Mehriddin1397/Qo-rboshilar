<?php

namespace Database\Factories;

use App\Enums\LiteratureStatus;
use App\Enums\LiteratureType;
use App\Models\Literature;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * DIQQAT: faqat DEMO/TEST ma'lumot — real tarixiy adabiyot emas.
 *
 * @extends Factory<Literature>
 */
class LiteratureFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = '[DEMO DATA] '.fake()->sentence(4);

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'title' => $title,
            'author' => '[DEMO DATA] '.fake()->name(),
            'publisher' => '[DEMO DATA] '.fake()->company(),
            'publication_year' => fake()->numberBetween(1900, 2020),
            'isbn' => fake()->isbn13(),
            'type' => fake()->randomElement(LiteratureType::cases()),
            'language' => "o'zbek",
            'description' => '[DEMO DATA] '.fake()->paragraphs(2, true),
            'status' => LiteratureStatus::Draft,
            'featured' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => LiteratureStatus::Published]);
    }
}
