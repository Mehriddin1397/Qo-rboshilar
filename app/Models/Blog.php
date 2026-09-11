<?php

namespace App\Models;

use App\Enums\BlogStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

class Blog extends Model
{
    /** @use HasFactory<\Database\Factories\BlogFactory> */
    use HasFactory;

    protected $table = 'bloglar';

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content',
        'cover_path',
        'status',
        'views',
        'published_at',
        'author_id',
        'meta_title',
        'meta_description',
        'og_image',
    ];

    protected function casts(): array
    {
        return [
            'status' => BlogStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * "Approved" — spec'dagi "published" bilan bir xil ma'no: tasdiqlangan blog
     * darhol public bo'ladi (§16 izohiga qarang, Faza 3'da qabul qilingan qaror).
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', BlogStatus::Approved);
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function qorboshilar(): BelongsToMany
    {
        return $this->belongsToMany(Qorboshi::class, 'blog_qorboshi');
    }

    public function uzgolonlar(): BelongsToMany
    {
        return $this->belongsToMany(Uzgolon::class, 'blog_uzgolon');
    }

    public function literatures(): BelongsToMany
    {
        return $this->belongsToMany(Literature::class, 'blog_literature');
    }

    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'blog_video');
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
