<?php

namespace App\Services;

use App\Enums\CommentStatus;
use App\Enums\ContentType;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CommentService
{
    /**
     * Whitelist orqali aniq modelga map qiladi va faqat published yozuvni qaytaradi
     * (draft/pending/rejected'ga izoh yozib bo'lmasligi — §25 — shu yerda kafolatlanadi).
     */
    public function resolveCommentable(string $type, int $id): Model
    {
        $modelClass = ContentType::from($type)->model();

        return $modelClass::query()->published()->findOrFail($id);
    }

    public function create(User $author, Model $commentable, string $content): Comment
    {
        return $commentable->comments()->create([
            'content' => $content,
            'status' => CommentStatus::Pending,
            'author_id' => $author->id,
        ]);
    }

    public function approve(Comment $comment): Comment
    {
        $comment->update(['status' => CommentStatus::Approved]);

        return $comment->fresh();
    }

    public function reject(Comment $comment): Comment
    {
        $comment->update(['status' => CommentStatus::Rejected]);

        return $comment->fresh();
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }

    /**
     * Detail sahifada ko'rsatiladigan approved commentlar — user eager load qilinadi,
     * N+1 oldini olish uchun (§38, §49).
     */
    public function approvedFor(Model $commentable, int $perPage = 10): LengthAwarePaginator
    {
        return $commentable->comments()
            ->approved()
            ->with('author')
            ->latest()
            ->paginate($perPage, ['*'], 'comments_page')
            ->withQueryString();
    }

    public function paginateForAdmin(Request $request): LengthAwarePaginator
    {
        return Comment::query()
            ->with(['author', 'commentable'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('type'), function ($q) use ($request) {
                $modelClass = ContentType::from($request->string('type')->toString())->model();
                $q->where('commentable_type', $modelClass);
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(function ($sub) use ($term) {
                    $sub->where('content', 'like', $term)
                        ->orWhereHas('author', fn ($a) => $a->where('name', 'like', $term)->orWhere('email', 'like', $term));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }
}
