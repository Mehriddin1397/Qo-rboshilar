<?php

namespace App\Models;

use App\Enums\RegionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    /** @use HasFactory<\Database\Factories\RegionFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'status' => RegionStatus::class,
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', RegionStatus::Published);
    }

    public function qorboshilar(): HasMany
    {
        return $this->hasMany(Qorboshi::class);
    }

    public function uzgolonlar(): HasMany
    {
        return $this->hasMany(Uzgolon::class);
    }

    public function historicalRegions(): HasMany
    {
        return $this->hasMany(HistoricalRegion::class);
    }

    public function timelineEvents(): HasMany
    {
        return $this->hasMany(TimelineEvent::class);
    }
}
