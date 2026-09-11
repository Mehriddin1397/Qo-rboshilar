<?php

namespace App\Models;

use App\Enums\HistoricalAccuracyStatus;
use App\Enums\HistoricalRegionStatus;
use App\Enums\HistoricalRegionType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class HistoricalRegion extends Model
{
    /** @use HasFactory<\Database\Factories\HistoricalRegionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'historical_name',
        'modern_name',
        'region_type',
        'description',
        'geojson',
        'period_id',
        'region_id',
        'status',
        'accuracy_status',
        'featured',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'geojson' => 'array',
            'status' => HistoricalRegionStatus::class,
            'region_type' => HistoricalRegionType::class,
            'accuracy_status' => HistoricalAccuracyStatus::class,
            'featured' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', HistoricalRegionStatus::Published);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function historicalMapLayers(): HasMany
    {
        return $this->hasMany(HistoricalMapLayer::class);
    }

    /**
     * Faza 10'dagi sourceables polymorphic pivot qayta ishlatiladi (§8) — real
     * tarixiy geometriya uchun manba talab qilinishi shu relation orqali tekshiriladi.
     */
    public function sourceReferences(): MorphToMany
    {
        return $this->morphToMany(SourceReference::class, 'sourceable');
    }
}
