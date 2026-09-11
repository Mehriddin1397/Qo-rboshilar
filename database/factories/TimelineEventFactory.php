<?php

namespace Database\Factories;

use App\Enums\HistoricalAccuracyStatus;
use App\Enums\TimelineEventStatus;
use App\Models\TimelineEvent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * DIQQAT: faqat DEMO/TEST ma'lumot — real tarixiy voqea emas.
 *
 * @extends Factory<TimelineEvent>
 */
class TimelineEventFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = '[DEMO] '.fake()->unique()->sentence(4).' (TEST DATA)';
        $startYear = fake()->numberBetween(1900, 1924);

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => '[DEMO DATA] '.fake()->paragraph(),
            'start_year' => $startYear,
            'end_year' => null,
            'event_date' => null,
            'status' => TimelineEventStatus::Draft,
            'accuracy_status' => HistoricalAccuracyStatus::Uncertain,
            'featured' => false,
            'sort_order' => 0,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => TimelineEventStatus::Published]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }

    public function verified(): static
    {
        return $this->state(fn () => ['accuracy_status' => HistoricalAccuracyStatus::Verified]);
    }

    public function withCoordinates(): static
    {
        return $this->state(fn () => [
            'latitude' => fake()->latitude(37, 45),
            'longitude' => fake()->longitude(56, 73),
        ]);
    }
}
