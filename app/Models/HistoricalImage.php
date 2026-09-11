<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class HistoricalImage extends Model
{
    /** @use HasFactory<\Database\Factories\HistoricalImageFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'file_path',
        'caption',
        'source',
        'source_url',
        'copyright',
        'year',
        'alt_text',
        'qorboshi_id',
        'uzgolon_id',
    ];

    public function url(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public function qorboshi(): BelongsTo
    {
        return $this->belongsTo(Qorboshi::class);
    }

    public function uzgolon(): BelongsTo
    {
        return $this->belongsTo(Uzgolon::class);
    }
}
