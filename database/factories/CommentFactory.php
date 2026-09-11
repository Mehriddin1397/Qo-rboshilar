<?php

namespace Database\Factories;

use App\Enums\CommentStatus;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * DIQQAT: faqat DEMO/TEST ma'lumot. `commentable` — factory `for()` orqali
 * beriladi (masalan `Comment::factory()->for($qorboshi, 'commentable')`).
 *
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => '[DEMO DATA] '.fake()->paragraph(2),
            'status' => CommentStatus::Pending,
            'author_id' => User::factory(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['status' => CommentStatus::Approved]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => ['status' => CommentStatus::Rejected]);
    }
}
