<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HistoricalAccuracyStatus;
use App\Enums\TimelineEventStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimelineEventRequest;
use App\Http\Requests\UpdateTimelineEventRequest;
use App\Models\HistoricalRegion;
use App\Models\Period;
use App\Models\Qorboshi;
use App\Models\Region;
use App\Models\SourceReference;
use App\Models\TimelineEvent;
use App\Models\Uzgolon;
use App\Services\TimelineEventService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TimelineEventController extends Controller
{
    public function __construct(private readonly TimelineEventService $timelineEventService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', TimelineEvent::class);

        return view('admin.timeline-events.index', [
            'title' => 'Xronologiya',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Xronologiya'],
            ],
            'items' => $this->timelineEventService->paginateForAdmin($request),
            'statuses' => TimelineEventStatus::cases(),
            'periods' => Period::orderBy('start_year')->get(),
            'filters' => $request->only(['search', 'status', 'featured', 'period_id', 'year']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', TimelineEvent::class);

        return view('admin.timeline-events.form', $this->formData(new TimelineEvent));
    }

    public function store(StoreTimelineEventRequest $request): RedirectResponse
    {
        $event = $this->timelineEventService->create($request->validated());

        $this->handleUploads($request, $event);

        return redirect()->route('admin.timeline-events.index')
            ->with('status', "\"{$event->title}\" muvaffaqiyatli qo'shildi.");
    }

    public function show(TimelineEvent $timelineEvent): View
    {
        $this->authorize('view', $timelineEvent);

        $timelineEvent->load(['period', 'qorboshi', 'uzgolon', 'region', 'historicalRegion', 'sourceReferences']);

        return view('admin.timeline-events.show', [
            'title' => $timelineEvent->title,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Xronologiya', 'url' => route('admin.timeline-events.index')],
                ['label' => $timelineEvent->title],
            ],
            'event' => $timelineEvent,
        ]);
    }

    public function edit(TimelineEvent $timelineEvent): View
    {
        $this->authorize('update', $timelineEvent);

        $timelineEvent->load('sourceReferences');

        return view('admin.timeline-events.form', $this->formData($timelineEvent));
    }

    public function update(UpdateTimelineEventRequest $request, TimelineEvent $timelineEvent): RedirectResponse
    {
        $event = $this->timelineEventService->update($timelineEvent, $request->validated());

        $this->handleUploads($request, $event);

        return redirect()->route('admin.timeline-events.index')->with('status', "\"{$event->title}\" yangilandi.");
    }

    public function destroy(TimelineEvent $timelineEvent): RedirectResponse
    {
        $this->authorize('delete', $timelineEvent);

        $title = $timelineEvent->title;
        $this->timelineEventService->delete($timelineEvent);

        return redirect()->route('admin.timeline-events.index')->with('status', "\"{$title}\" o'chirildi.");
    }

    private function handleUploads(Request $request, TimelineEvent $event): void
    {
        if ($request->hasFile('image')) {
            if ($event->image_path) {
                Storage::disk('public')->delete($event->image_path);
            }

            $event->update(['image_path' => $request->file('image')->store('timeline-events', 'public')]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(TimelineEvent $event): array
    {
        return [
            'title' => $event->exists ? "Tahrirlash — {$event->title}" : 'Yangi xronologiya voqeasi',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Xronologiya', 'url' => route('admin.timeline-events.index')],
                ['label' => $event->exists ? 'Tahrirlash' : 'Yangi'],
            ],
            'event' => $event,
            'statuses' => TimelineEventStatus::cases(),
            'accuracyStatuses' => HistoricalAccuracyStatus::cases(),
            'periods' => Period::orderBy('start_year')->get(),
            'qorboshilar' => Qorboshi::orderBy('full_name')->get(),
            'uzgolonlar' => Uzgolon::orderBy('name')->get(),
            'regions' => Region::orderBy('name')->get(),
            'historicalRegions' => HistoricalRegion::orderBy('name')->get(),
            'sourceReferences' => SourceReference::orderBy('title')->get(),
        ];
    }
}
