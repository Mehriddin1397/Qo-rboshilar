<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Period;
use App\Services\CommentService;
use App\Services\TimelineEventService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TimelineEventController extends Controller
{
    public function __construct(
        private readonly TimelineEventService $timelineEventService,
        private readonly CommentService $commentService,
    ) {
    }

    public function index(Request $request): View
    {
        return view('xronologiya.index', [
            'items' => $this->timelineEventService->paginateForPublic($request),
            'periods' => Period::orderBy('start_year')->get(),
            'filters' => $request->only(['q', 'period', 'from_year', 'to_year']),
            'seo' => [
                'title' => "Tarixiy xronologiya — Qo'rboshilar.uz",
                'description' => "Turkiston tarixidagi muhim voqealar yillar bo'yicha.",
                'canonical' => route('xronologiya.index'),
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $event = $this->timelineEventService->findPublishedBySlugOrFail($slug);

        return view('xronologiya.show', [
            'event' => $event,
            'comments' => $this->commentService->approvedFor($event),
            'seo' => [
                'title' => "{$event->title} — Qo'rboshilar.uz",
                'description' => Str::limit($event->description, 160),
                'canonical' => route('xronologiya.show', $event),
            ],
        ]);
    }
}
