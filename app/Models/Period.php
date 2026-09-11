<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
{
    /** @use HasFactory<\Database\Factories\PeriodFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'start_year',
        'end_year',
        'description',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function uzgolonlar(): HasMany
    {
        return $this->hasMany(Uzgolon::class);
    }

    public function historicalRegions(): HasMany
    {
        return $this->hasMany(HistoricalRegion::class);
    }

    public function historicalMapLayers(): HasMany
    {
        return $this->hasMany(HistoricalMapLayer::class);
    }

    public function timelineEvents(): HasMany
    {
        return $this->hasMany(TimelineEvent::class);
    }
}
