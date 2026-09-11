<?php

namespace Database\Factories;

use App\Models\HistoricalImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * DIQQAT: faqat DEMO/TEST ma'lumot — real tarixiy rasm/manba emas.
 *
 * @extends Factory<HistoricalImage>
 */
class HistoricalImageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => '[DEMO DATA] '.fake()->sentence(3),
            'file_path' => 'historical-images/demo-placeholder.jpg',
            'caption' => '[DEMO DATA] '.fake()->sentence(),
            'source' => null,
            'source_url' => null,
            'copyright' => null,
            'year' => fake()->numberBetween(1900, 1930),
            'alt_text' => '[DEMO DATA] '.fake()->words(3, true),
        ];
    }
}
