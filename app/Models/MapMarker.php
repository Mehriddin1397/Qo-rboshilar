<?php

namespace App\Models;

use App\Enums\MapMarkerStatus;
use App\Enums\MapMarkerType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapMarker extends Model
{
    /** @use HasFactory<\Database\Factories\MapMarkerFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'latitude',
        'longitude',
        'type',
        'description',
        'uzgolon_id',
        'status',
        'is_primary',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'type' => MapMarkerType::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'status' => MapMarkerStatus::class,
            'is_primary' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', MapMarkerStatus::Published);
    }

    public function uzgolon(): BelongsTo
    {
        return $this->belongsTo(Uzgolon::class);
    }
}
