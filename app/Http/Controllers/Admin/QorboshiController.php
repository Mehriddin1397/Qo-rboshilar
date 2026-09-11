<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\QorboshiStatus;
use App\Http\Requests\StoreQorboshiRequest;
use App\Http\Requests\UpdateQorboshiRequest;
use App\Models\HistoricalImage;
use App\Models\Literature;
use App\Models\Qorboshi;
use App\Models\Region;
use App\Models\SourceReference;
use App\Models\Uzgolon;
use App\Services\QorboshiMediaService;
use App\Services\QorboshiService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QorboshiController extends Controller
{
    public function __construct(
        private readonly QorboshiService $qorboshiService,
        private readonly QorboshiMediaService $mediaService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Qorboshi::class);

        return view('admin.qorboshilar.index', [
            'title' => "Qo'rboshilar",
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => "Qo'rboshilar"],
            ],
            'items' => $this->qorboshiService->paginateForAdmin($request),
            'regions' => Region::orderBy('name')->get(),
            'statuses' => QorboshiStatus::cases(),
            'filters' => $request->only(['search', 'status', 'featured', 'region_id']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Qorboshi::class);

        return view('admin.qorboshilar.form', $this->formData(new Qorboshi));
    }

    public function store(StoreQorboshiRequest $request): RedirectResponse
    {
        $qorboshi = $this->qorboshiService->create($request->validated());

        $this->handleUploads($request, $qorboshi);

        return redirect()->route('admin.qorboshilar.index')->with('status', "\"{$qorboshi->full_name}\" muvaffaqiyatli qo'shildi.");
    }

    public function show(Qorboshi $qorboshi): View
    {
        $this->authorize('view', $qorboshi);

        $qorboshi->load(['region', 'uzgolonlar', 'literatures', 'videos', 'images', 'sourceReferences']);

        return view('admin.qorboshilar.show', [
            'title' => $qorboshi->full_name,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => "Qo'rboshilar", 'url' => route('admin.qorboshilar.index')],
                ['label' => $qorboshi->full_name],
            ],
            'qorboshi' => $qorboshi,
        ]);
    }

    public function edit(Qorboshi $qorboshi): View
    {
        $this->authorize('update', $qorboshi);

        $qorboshi->load(['uzgolonlar', 'literatures', 'sourceReferences', 'images']);

        return view('admin.qorboshilar.form', $this->formData($qorboshi));
    }

    public function update(UpdateQorboshiRequest $request, Qorboshi $qorboshi): RedirectResponse
    {
        $qorboshi = $this->qorboshiService->update($qorboshi, $request->validated());

        $this->handleUploads($request, $qorboshi);

        return redirect()->route('admin.qorboshilar.index')->with('status', "\"{$qorboshi->full_name}\" yangilandi.");
    }

    public function destroy(Qorboshi $qorboshi): RedirectResponse
    {
        $this->authorize('delete', $qorboshi);

        $name = $qorboshi->full_name;
        $this->qorboshiService->delete($qorboshi);

        return redirect()->route('admin.qorboshilar.index')->with('status', "\"{$name}\" o'chirildi.");
    }

    public function destroyGalleryImage(Qorboshi $qorboshi, HistoricalImage $image): RedirectResponse
    {
        $this->authorize('update', $qorboshi);
        abort_unless($image->qorboshi_id === $qorboshi->id, 404);

        $this->mediaService->deleteGalleryImage($image);

        return back()->with('status', 'Rasm o\'chirildi.');
    }

    private function handleUploads(Request $request, Qorboshi $qorboshi): void
    {
        if ($request->hasFile('portrait')) {
            $qorboshi->update(['portrait_path' => $this->mediaService->storePortrait($qorboshi, $request->file('portrait'))]);
        }

        foreach ($request->file('gallery', []) as $file) {
            $this->mediaService->addGalleryImage($qorboshi, $file);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Qorboshi $qorboshi): array
    {
        return [
            'title' => $qorboshi->exists ? "Tahrirlash — {$qorboshi->full_name}" : "Yangi qo'rboshi",
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => "Qo'rboshilar", 'url' => route('admin.qorboshilar.index')],
                ['label' => $qorboshi->exists ? 'Tahrirlash' : 'Yangi'],
            ],
            'qorboshi' => $qorboshi,
            'regions' => Region::orderBy('name')->get(),
            'uzgolonlar' => Uzgolon::orderBy('name')->get(),
            'literatures' => Literature::orderBy('title')->get(),
            'sourceReferences' => SourceReference::orderBy('title')->get(),
            'statuses' => QorboshiStatus::cases(),
        ];
    }
}
