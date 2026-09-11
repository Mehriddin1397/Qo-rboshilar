<?php

namespace App\Models;

use App\Enums\SourceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class SourceReference extends Model
{
    /** @use HasFactory<\Database\Factories\SourceReferenceFactory> */
    use HasFactory;

    protected $fillable = [
        'author',
        'title',
        'publisher',
        'year',
        'url',
        'page',
        'note',
        'source_type',
        'literature_id',
    ];

    protected function casts(): array
    {
        return [
            'source_type' => SourceType::class,
        ];
    }

    public function literature(): BelongsTo
    {
        return $this->belongsTo(Literature::class);
    }

    public function qorboshilar(): MorphToMany
    {
        return $this->morphedByMany(Qorboshi::class, 'sourceable');
    }

    public function uzgolonlar(): MorphToMany
    {
        return $this->morphedByMany(Uzgolon::class, 'sourceable');
    }

    public function videos(): MorphToMany
    {
        return $this->morphedByMany(Video::class, 'sourceable');
    }

    public function blogs(): MorphToMany
    {
        return $this->morphedByMany(Blog::class, 'sourceable');
    }

    public function historicalRegions(): MorphToMany
    {
        return $this->morphedByMany(HistoricalRegion::class, 'sourceable');
    }

    public function historicalMapLayers(): MorphToMany
    {
        return $this->morphedByMany(HistoricalMapLayer::class, 'sourceable');
    }

    public function timelineEvents(): MorphToMany
    {
        return $this->morphedByMany(TimelineEvent::class, 'sourceable');
    }
}
