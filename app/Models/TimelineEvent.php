<?php

namespace App\Models;

use App\Enums\HistoricalAccuracyStatus;
use App\Enums\TimelineEventStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

class TimelineEvent extends Model
{
    /** @use HasFactory<\Database\Factories\TimelineEventFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'start_year',
        'end_year',
        'event_date',
        'image_path',
        'status',
        'accuracy_status',
        'featured',
        'sort_order',
        'period_id',
        'qorboshi_id',
        'uzgolon_id',
        'region_id',
        'historical_region_id',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'status' => TimelineEventStatus::class,
            'accuracy_status' => HistoricalAccuracyStatus::class,
            'featured' => 'boolean',
            'start_year' => 'integer',
            'end_year' => 'integer',
            'sort_order' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', TimelineEventStatus::Published);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    /**
     * Yil oralig'ini o'qish uchun qulay format — "1918" yoki "1918–1924".
     * Soxta kun/oy aniqligi yaratilmaydi (§4).
     */
    public function yearRangeLabel(): string
    {
        if ($this->end_year && $this->end_year !== $this->start_year) {
            return "{$this->start_year}–{$this->end_year}";
        }

        return (string) $this->start_year;
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function qorboshi(): BelongsTo
    {
        return $this->belongsTo(Qorboshi::class);
    }

    public function uzgolon(): BelongsTo
    {
        return $this->belongsTo(Uzgolon::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function historicalRegion(): BelongsTo
    {
        return $this->belongsTo(HistoricalRegion::class);
    }

    /**
     * Faza 10'dagi sourceables polymorphic pivot qayta ishlatiladi (§7) — parallel
     * source tizimi yaratilmadi.
     */
    public function sourceReferences(): MorphToMany
    {
        return $this->morphToMany(SourceReference::class, 'sourceable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
