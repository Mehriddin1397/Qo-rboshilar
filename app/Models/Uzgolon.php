<?php

namespace App\Models;

use App\Enums\UzgolonStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

class Uzgolon extends Model
{
    /** @use HasFactory<\Database\Factories\UzgolonFactory> */
    use HasFactory;

    protected $table = 'uzgolonlar';

    protected $fillable = [
        'slug',
        'name',
        'start_year',
        'end_year',
        'short_description',
        'historical_context',
        'causes',
        'main_events',
        'results',
        'historical_significance',
        'cover_image',
        'background_image',
        'region_id',
        'period_id',
        'historical_location',
        'modern_location',
        'status',
        'featured',
        'meta_title',
        'meta_description',
        'og_image',
    ];

    protected function casts(): array
    {
        return [
            'status' => UzgolonStatus::class,
            'featured' => 'boolean',
            'start_year' => 'integer',
            'end_year' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', UzgolonStatus::Published);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function coverImageUrl(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }

    public function backgroundImageUrl(): ?string
    {
        return $this->background_image ? Storage::disk('public')->url($this->background_image) : null;
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function qorboshilar(): BelongsToMany
    {
        return $this->belongsToMany(Qorboshi::class, 'qorboshi_uzgolon');
    }

    public function literatures(): BelongsToMany
    {
        return $this->belongsToMany(Literature::class, 'uzgolon_literature');
    }

    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_uzgolon');
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

    public function mapMarkers(): HasMany
    {
        return $this->hasMany(MapMarker::class);
    }

    /**
     * Bitta qo'zg'olon bir nechta marker (masalan turli janglar joyi)ga ega bo'lishi
     * mumkin (`mapMarkers()`), lekin faqat bittasi `is_primary = true` bo'la oladi —
     * shu marker Uzgolon formasidagi lat/long va public /xarita'dagi "uprising"
     * nuqtasi sifatida ishlatiladi (MapMarkerService bitta uzgolon uchun bir vaqtning
     * o'zida faqat bitta primary marker qolishini kafolatlaydi).
     */
    public function primaryMarker(): HasOne
    {
        return $this->hasOne(MapMarker::class)->where('is_primary', true);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
