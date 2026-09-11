<?php

namespace App\Models;

use App\Enums\LiteratureStatus;
use App\Enums\LiteratureType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Literature extends Model
{
    /** @use HasFactory<\Database\Factories\LiteratureFactory> */
    use HasFactory;

    protected $table = 'adabiyotlar';

    protected $fillable = [
        'slug',
        'title',
        'author',
        'publisher',
        'publication_year',
        'isbn',
        'type',
        'language',
        'description',
        'cover_path',
        'source_url',
        'file_path',
        'status',
        'featured',
        'meta_title',
        'meta_description',
        'og_image',
    ];

    protected function casts(): array
    {
        return [
            'type' => LiteratureType::class,
            'status' => LiteratureStatus::class,
            'featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', LiteratureStatus::Published);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function coverUrl(): ?string
    {
        return $this->cover_path ? Storage::disk('public')->url($this->cover_path) : null;
    }

    public function fileUrl(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }

    public function qorboshilar(): BelongsToMany
    {
        return $this->belongsToMany(Qorboshi::class, 'qorboshi_literature');
    }

    public function uzgolonlar(): BelongsToMany
    {
        return $this->belongsToMany(Uzgolon::class, 'uzgolon_literature');
    }

    public function blogs(): BelongsToMany
    {
        return $this->belongsToMany(Blog::class, 'blog_literature');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    /**
     * Faza 3'dan: bu — Literature'ning "manba sifatida" tomoni (boshqa yozuvlar
     * shu adabiyotni SourceReference orqali keltiradi, `source_references.literature_id`
     * FK orqali). Bu Qorboshi/Uzgolon'dagi `sourceables` (morphToMany) bilan bir xil
     * emas — u yerda "bu yozuv qaysi manbalarga asoslangan" ma'nosida, bu yerda esa
     * "bu adabiyot boshqa yozuvlar uchun manba" ma'nosida. Ikkalasi ham to'g'ri va
     * bir-biriga zid emas, shuning uchun o'zgartirilmadi.
     */
    public function sourceReferences(): HasMany
    {
        return $this->hasMany(SourceReference::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
