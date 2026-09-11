<?php

namespace App\Enums;

use App\Models\Blog;
use App\Models\Literature;
use App\Models\Qorboshi;
use App\Models\TimelineEvent;
use App\Models\Uzgolon;
use App\Models\Video;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/**
 * Global Search (Faza 11 §8) va Comment polymorphic whitelist (Faza 11 §24) uchun
 * bitta umumiy manba — barchasi xuddi shu content turlarini bilishi kerak,
 * shuning uchun mapping bir joyda saqlanadi (duplicate whitelist yo'q).
 * TimelineEvent — Faza 14 §20-21'da qo'shildi.
 */
enum ContentType: string
{
    case Qorboshi = 'qorboshi';
    case Uzgolon = 'uzgolon';
    case Literature = 'literature';
    case Video = 'video';
    case Blog = 'blog';
    case TimelineEvent = 'timeline_event';

    public function label(): string
    {
        return match ($this) {
            self::Qorboshi => "Qo'rboshi",
            self::Uzgolon => "Qo'zg'olon",
            self::Literature => 'Adabiyot',
            self::Video => 'Video',
            self::Blog => 'Blog',
            self::TimelineEvent => 'Xronologiya voqeasi',
        };
    }

    public function pluralLabel(): string
    {
        return match ($this) {
            self::Qorboshi => "Qo'rboshilar",
            self::Uzgolon => "Qo'zg'olonlar",
            self::Literature => 'Adabiyotlar',
            self::Video => 'Videolar',
            self::Blog => 'Bloglar',
            self::TimelineEvent => 'Xronologiya',
        };
    }

    /**
     * @return class-string<Model>
     */
    public function model(): string
    {
        return match ($this) {
            self::Qorboshi => Qorboshi::class,
            self::Uzgolon => Uzgolon::class,
            self::Literature => Literature::class,
            self::Video => Video::class,
            self::Blog => Blog::class,
            self::TimelineEvent => TimelineEvent::class,
        };
    }

    public function routeName(): string
    {
        return match ($this) {
            self::Qorboshi => 'qorboshilar.show',
            self::Uzgolon => 'qozgolonlar.show',
            self::Literature => 'adabiyotlar.show',
            self::Video => 'videolar.show',
            self::Blog => 'bloglar.show',
            self::TimelineEvent => 'xronologiya.show',
        };
    }

    public static function fromModel(Model $model): self
    {
        foreach (self::cases() as $case) {
            if ($model instanceof ($case->model())) {
                return $case;
            }
        }

        throw new InvalidArgumentException('Unsupported commentable/searchable model: '.$model::class);
    }
}
