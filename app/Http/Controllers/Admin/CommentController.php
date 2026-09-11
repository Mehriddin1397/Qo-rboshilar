<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommentStatus;
use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $commentService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Comment::class);

        return view('admin.comments.index', [
            'title' => 'Kommentlar',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Kommentlar'],
            ],
            'items' => $this->commentService->paginateForAdmin($request),
            'statuses' => CommentStatus::cases(),
            'types' => ContentType::cases(),
            'filters' => $request->only(['status', 'type', 'search']),
        ]);
    }

    public function show(Comment $comment): View
    {
        $this->authorize('view', $comment);

        $comment->load(['author', 'commentable']);

        return view('admin.comments.show', [
            'title' => 'Izoh',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Kommentlar', 'url' => route('admin.comments.index')],
                ['label' => "Izoh #{$comment->id}"],
            ],
            'comment' => $comment,
        ]);
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $this->authorize('moderate', $comment);

        $this->commentService->approve($comment);

        return back()->with('status', 'Izoh tasdiqlandi.');
    }

    public function reject(Comment $comment): RedirectResponse
    {
        $this->authorize('moderate', $comment);

        $this->commentService->reject($comment);

        return back()->with('status', 'Izoh rad etildi.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $this->commentService->delete($comment);

        return redirect()->route('admin.comments.index')->with('status', "Izoh o'chirildi.");
    }
}
