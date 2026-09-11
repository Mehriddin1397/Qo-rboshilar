<?php

namespace App\Models;

use App\Enums\VideoCategory;
use App\Enums\VideoStatus;
use App\Support\YoutubeUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

class Video extends Model
{
    /** @use HasFactory<\Database\Factories\VideoFactory> */
    use HasFactory;

    protected $table = 'videolar';

    protected $fillable = [
        'slug',
        'title',
        'youtube_url',
        'youtube_id',
        'category',
        'duration_seconds',
        'description',
        'thumbnail_path',
        'qorboshi_id',
        'uzgolon_id',
        'literature_id',
        'status',
        'featured',
        'sources',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'category' => VideoCategory::class,
            'status' => VideoStatus::class,
            'featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', VideoStatus::Published);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /** Faqat qattiq YouTube domenidan quriladi — hech qachon xom foydalanuvchi HTML emas. */
    public function embedUrl(): ?string
    {
        return $this->youtube_id ? YoutubeUrl::embedUrl($this->youtube_id) : null;
    }

    public function thumbnailUrl(): ?string
    {
        if ($this->thumbnail_path) {
            return Storage::disk('public')->url($this->thumbnail_path);
        }

        return $this->youtube_id ? YoutubeUrl::thumbnailUrl($this->youtube_id) : null;
    }

    public function qorboshi(): BelongsTo
    {
        return $this->belongsTo(Qorboshi::class);
    }

    public function uzgolon(): BelongsTo
    {
        return $this->belongsTo(Uzgolon::class);
    }

    public function literature(): BelongsTo
    {
        return $this->belongsTo(Literature::class);
    }

    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_video');
    }

    public function sourceReferences(): MorphToMany
    {
        return $this->morphToMany(SourceReference::class, 'sourceable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
