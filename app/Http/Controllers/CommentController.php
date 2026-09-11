<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\RedirectResponse;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $commentService)
    {
    }

    public function store(StoreCommentRequest $request): RedirectResponse
    {
        $commentable = $this->commentService->resolveCommentable(
            $request->validated('commentable_type'),
            (int) $request->validated('commentable_id'),
        );

        $this->commentService->create($request->user(), $commentable, $request->validated('content'));

        return back()->with('status', 'Izohingiz moderatsiyaga yuborildi.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $this->commentService->delete($comment);

        return back()->with('status', "Izoh o'chirildi.");
    }
}
