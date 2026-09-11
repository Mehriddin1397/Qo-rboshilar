<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UzgolonStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUzgolonRequest;
use App\Http\Requests\UpdateUzgolonRequest;
use App\Models\HistoricalImage;
use App\Models\Literature;
use App\Models\Period;
use App\Models\Qorboshi;
use App\Models\Region;
use App\Models\SourceReference;
use App\Models\Uzgolon;
use App\Services\UzgolonMediaService;
use App\Services\UzgolonService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UzgolonController extends Controller
{
    public function __construct(
        private readonly UzgolonService $uzgolonService,
        private readonly UzgolonMediaService $mediaService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Uzgolon::class);

        return view('admin.qozgolonlar.index', [
            'title' => "Qo'zg'olonlar",
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => "Qo'zg'olonlar"],
            ],
            'items' => $this->uzgolonService->paginateForAdmin($request),
            'regions' => Region::orderBy('name')->get(),
            'periods' => Period::orderBy('start_year')->get(),
            'statuses' => UzgolonStatus::cases(),
            'filters' => $request->only(['search', 'status', 'featured', 'region_id', 'period_id']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Uzgolon::class);

        return view('admin.qozgolonlar.form', $this->formData(new Uzgolon));
    }

    public function store(StoreUzgolonRequest $request): RedirectResponse
    {
        $uzgolon = $this->uzgolonService->create($request->validated());

        $this->handleUploads($request, $uzgolon);

        return redirect()->route('admin.qozgolonlar.index')->with('status', "\"{$uzgolon->name}\" muvaffaqiyatli qo'shildi.");
    }

    public function show(Uzgolon $qozgolon): View
    {
        $this->authorize('view', $qozgolon);

        $qozgolon->load(['region', 'period', 'qorboshilar', 'literatures', 'videos', 'images', 'sourceReferences', 'primaryMarker']);

        return view('admin.qozgolonlar.show', [
            'title' => $qozgolon->name,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => "Qo'zg'olonlar", 'url' => route('admin.qozgolonlar.index')],
                ['label' => $qozgolon->name],
            ],
            'uzgolon' => $qozgolon,
        ]);
    }

    public function edit(Uzgolon $qozgolon): View
    {
        $this->authorize('update', $qozgolon);

        $qozgolon->load(['qorboshilar', 'literatures', 'sourceReferences', 'images', 'primaryMarker']);

        return view('admin.qozgolonlar.form', $this->formData($qozgolon));
    }

    public function update(UpdateUzgolonRequest $request, Uzgolon $qozgolon): RedirectResponse
    {
        $qozgolon = $this->uzgolonService->update($qozgolon, $request->validated());

        $this->handleUploads($request, $qozgolon);

        return redirect()->route('admin.qozgolonlar.index')->with('status', "\"{$qozgolon->name}\" yangilandi.");
    }

    public function destroy(Uzgolon $qozgolon): RedirectResponse
    {
        $this->authorize('delete', $qozgolon);

        $name = $qozgolon->name;
        $this->uzgolonService->delete($qozgolon);

        return redirect()->route('admin.qozgolonlar.index')->with('status', "\"{$name}\" o'chirildi.");
    }

    public function destroyGalleryImage(Uzgolon $qozgolon, HistoricalImage $image): RedirectResponse
    {
        $this->authorize('update', $qozgolon);
        abort_unless($image->uzgolon_id === $qozgolon->id, 404);

        $this->mediaService->deleteGalleryImage($image);

        return back()->with('status', "Rasm o'chirildi.");
    }

    private function handleUploads(Request $request, Uzgolon $uzgolon): void
    {
        if ($request->hasFile('cover')) {
            $uzgolon->update(['cover_image' => $this->mediaService->storeCover($uzgolon, $request->file('cover'))]);
        }

        foreach ($request->file('gallery', []) as $file) {
            $this->mediaService->addGalleryImage($uzgolon, $file);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Uzgolon $uzgolon): array
    {
        return [
            'title' => $uzgolon->exists ? "Tahrirlash — {$uzgolon->name}" : "Yangi qo'zg'olon",
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => "Qo'zg'olonlar", 'url' => route('admin.qozgolonlar.index')],
                ['label' => $uzgolon->exists ? 'Tahrirlash' : 'Yangi'],
            ],
            'uzgolon' => $uzgolon,
            'regions' => Region::orderBy('name')->get(),
            'periods' => Period::orderBy('start_year')->get(),
            'qorboshilar' => Qorboshi::orderBy('full_name')->get(),
            'literatures' => Literature::orderBy('title')->get(),
            'sourceReferences' => SourceReference::orderBy('title')->get(),
            'statuses' => UzgolonStatus::cases(),
        ];
    }
}
