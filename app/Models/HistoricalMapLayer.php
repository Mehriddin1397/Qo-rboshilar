<?php

namespace App\Models;

use App\Enums\HistoricalAccuracyStatus;
use App\Enums\HistoricalMapLayerStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;

class HistoricalMapLayer extends Model
{
    /** @use HasFactory<\Database\Factories\HistoricalMapLayerFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'period_id',
        'historical_region_id',
        'image_path',
        'opacity',
        'bounds',
        'geojson',
        'is_active',
        'status',
        'accuracy_status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'bounds' => 'array',
            'geojson' => 'array',
            'is_active' => 'boolean',
            'opacity' => 'float',
            'status' => HistoricalMapLayerStatus::class,
            'accuracy_status' => HistoricalAccuracyStatus::class,
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', HistoricalMapLayerStatus::Published);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function historicalRegion(): BelongsTo
    {
        return $this->belongsTo(HistoricalRegion::class);
    }

    /**
     * Faza 10'dagi sourceables polymorphic pivot qayta ishlatiladi (§20) — alohida
     * source_title/source_url ustunlari yoki parallel source tizimi qo'shilmadi.
     */
    public function sourceReferences(): MorphToMany
    {
        return $this->morphToMany(SourceReference::class, 'sourceable');
    }
}
