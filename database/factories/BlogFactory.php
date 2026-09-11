<?php

namespace Database\Factories;

use App\Enums\BlogStatus;
use App\Models\Blog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * DIQQAT: faqat DEMO/TEST ma'lumot — real tarixiy maqola emas.
 *
 * @extends Factory<Blog>
 */
class BlogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = '[DEMO DATA] '.fake()->sentence(6);

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'title' => $title,
            'excerpt' => '[DEMO DATA] '.fake()->sentence(15),
            'content' => "[DEMO DATA] Bu — test uchun avtomatik yaratilgan blog matni.\n\n".fake()->paragraphs(4, true),
            'status' => BlogStatus::Draft,
            'views' => 0,
            'author_id' => User::factory(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => BlogStatus::Approved, 'published_at' => now()]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => BlogStatus::Pending]);
    }
}
