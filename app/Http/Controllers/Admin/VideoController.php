<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VideoCategory;
use App\Enums\VideoStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVideoRequest;
use App\Http\Requests\UpdateVideoRequest;
use App\Models\Literature;
use App\Models\Qorboshi;
use App\Models\SourceReference;
use App\Models\Uzgolon;
use App\Models\Video;
use App\Services\VideoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function __construct(private readonly VideoService $videoService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Video::class);

        return view('admin.videolar.index', [
            'title' => 'Videolar',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Videolar'],
            ],
            'items' => $this->videoService->paginateForAdmin($request),
            'categories' => VideoCategory::cases(),
            'statuses' => VideoStatus::cases(),
            'filters' => $request->only(['search', 'category', 'status', 'featured']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Video::class);

        return view('admin.videolar.form', $this->formData(new Video));
    }

    public function store(StoreVideoRequest $request): RedirectResponse
    {
        $video = $this->videoService->create($request->validated());

        $this->handleUploads($request, $video);

        return redirect()->route('admin.videolar.index')->with('status', "\"{$video->title}\" muvaffaqiyatli qo'shildi.");
    }

    public function show(Video $video): View
    {
        $this->authorize('view', $video);

        $video->load(['qorboshi', 'uzgolon', 'literature', 'sourceReferences']);

        return view('admin.videolar.show', [
            'title' => $video->title,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Videolar', 'url' => route('admin.videolar.index')],
                ['label' => $video->title],
            ],
            'video' => $video,
        ]);
    }

    public function edit(Video $video): View
    {
        $this->authorize('update', $video);

        $video->load(['sourceReferences']);

        return view('admin.videolar.form', $this->formData($video));
    }

    public function update(UpdateVideoRequest $request, Video $video): RedirectResponse
    {
        $video = $this->videoService->update($video, $request->validated());

        $this->handleUploads($request, $video);

        return redirect()->route('admin.videolar.index')->with('status', "\"{$video->title}\" yangilandi.");
    }

    public function destroy(Video $video): RedirectResponse
    {
        $this->authorize('delete', $video);

        $title = $video->title;
        $this->videoService->delete($video);

        return redirect()->route('admin.videolar.index')->with('status', "\"{$title}\" o'chirildi.");
    }

    private function handleUploads(Request $request, Video $video): void
    {
        if ($request->hasFile('thumbnail')) {
            if ($video->thumbnail_path) {
                Storage::disk('public')->delete($video->thumbnail_path);
            }

            $video->update(['thumbnail_path' => $request->file('thumbnail')->store('videolar/thumbnails', 'public')]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Video $video): array
    {
        return [
            'title' => $video->exists ? "Tahrirlash — {$video->title}" : "Yangi video",
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Videolar', 'url' => route('admin.videolar.index')],
                ['label' => $video->exists ? 'Tahrirlash' : 'Yangi'],
            ],
            'video' => $video,
            'categories' => VideoCategory::cases(),
            'statuses' => VideoStatus::cases(),
            'qorboshilar' => Qorboshi::orderBy('full_name')->get(),
            'uzgolonlar' => Uzgolon::orderBy('name')->get(),
            'literatures' => Literature::orderBy('title')->get(),
            'sourceReferences' => SourceReference::orderBy('title')->get(),
        ];
    }
}
