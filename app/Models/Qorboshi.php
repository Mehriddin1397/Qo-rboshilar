<?php

namespace App\Models;

use App\Enums\QorboshiStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

class Qorboshi extends Model
{
    /** @use HasFactory<\Database\Factories\QorboshiFactory> */
    use HasFactory;

    protected $table = 'qorboshilar';

    protected $fillable = [
        'slug',
        'full_name',
        'short_description',
        'biography',
        'historical_context',
        'birth_year',
        'birth_place',
        'death_year',
        'death_place',
        'active_from_year',
        'active_to_year',
        'portrait_path',
        'region_id',
        'status',
        'featured',
        'meta_title',
        'meta_description',
        'og_image',
    ];

    protected function casts(): array
    {
        return [
            'status' => QorboshiStatus::class,
            'featured' => 'boolean',
            'birth_year' => 'integer',
            'death_year' => 'integer',
            'active_from_year' => 'integer',
            'active_to_year' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function portraitUrl(): string
    {
        return $this->portrait_path
            ? Storage::disk('public')->url($this->portrait_path)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->full_name).'&background=E4D6B8&color=3B2A1D&size=256';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', QorboshiStatus::Published);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function uzgolonlar(): BelongsToMany
    {
        return $this->belongsToMany(Uzgolon::class, 'qorboshi_uzgolon');
    }

    public function literatures(): BelongsToMany
    {
        return $this->belongsToMany(Literature::class, 'qorboshi_literature');
    }

    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_qorboshi');
    }

    public function sourceReferences(): MorphToMany
    {
        return $this->morphToMany(SourceReference::class, 'sourceable');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(HistoricalImage::class);
    }

    public function timelineEvents(): HasMany
    {
        return $this->hasMany(TimelineEvent::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
