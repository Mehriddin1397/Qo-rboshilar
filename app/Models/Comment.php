<?php

namespace App\Models;

use App\Enums\CommentStatus;
use App\Enums\ContentType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    /** @use HasFactory<\Database\Factories\CommentFactory> */
    use HasFactory;

    protected $fillable = [
        'content',
        'status',
        'author_id',
        'commentable_type',
        'commentable_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => CommentStatus::class,
        ];
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Approved);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', CommentStatus::Pending);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Admin panelda ko'rsatish uchun — har bir content turi o'z "sarlavha" maydoniga
     * ega (full_name/name/title), shu farqni Blade'ga chiqarmaslik uchun shu yerda.
     */
    public function commentableTitle(): string
    {
        $model = $this->commentable;

        if (! $model) {
            return '—';
        }

        return match (true) {
            $model instanceof Qorboshi => $model->full_name,
            $model instanceof Uzgolon => $model->name,
            default => $model->title ?? '—',
        };
    }

    public function commentableTypeLabel(): string
    {
        return $this->commentable ? ContentType::fromModel($this->commentable)->label() : '—';
    }

    public function commentableUrl(): ?string
    {
        if (! $this->commentable) {
            return null;
        }

        $type = ContentType::fromModel($this->commentable);

        return route($type->routeName(), $this->commentable->slug);
    }
}
