<?php

namespace Database\Factories;

use App\Enums\VideoCategory;
use App\Enums\VideoStatus;
use App\Models\Video;
use App\Support\YoutubeUrl;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * DIQQAT: faqat DEMO/TEST ma'lumot. YouTube havolasi ham TEST maqsadida —
 * YouTube'ning eng birinchi ("Me at the zoo") ochiq videosi, hech qanday
 * Turkiston tarixi bilan bog'liq emas — faqat embed/YouTube ID ajratish
 * mexanizmini tekshirish uchun ishlatiladi.
 *
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    private const DEMO_YOUTUBE_URL = 'https://www.youtube.com/watch?v=jNQXAC9IVRw';

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = '[DEMO DATA] '.fake()->sentence(4);

        return [
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'title' => $title,
            'youtube_url' => self::DEMO_YOUTUBE_URL,
            'youtube_id' => YoutubeUrl::extractId(self::DEMO_YOUTUBE_URL),
            'category' => fake()->randomElement(VideoCategory::cases()),
            'description' => '[DEMO/TEST DATA] '.fake()->paragraph(),
            'status' => VideoStatus::Draft,
            'featured' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => ['status' => VideoStatus::Published]);
    }
}
