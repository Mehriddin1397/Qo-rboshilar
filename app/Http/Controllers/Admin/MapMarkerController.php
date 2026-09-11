<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MapMarkerStatus;
use App\Enums\MapMarkerType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMapMarkerRequest;
use App\Http\Requests\UpdateMapMarkerRequest;
use App\Models\MapMarker;
use App\Models\Uzgolon;
use App\Services\MapMarkerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MapMarkerController extends Controller
{
    public function __construct(private readonly MapMarkerService $mapMarkerService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MapMarker::class);

        return view('admin.map-markers.index', [
            'title' => 'Xarita markerlari',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Xarita markerlari'],
            ],
            'items' => $this->mapMarkerService->paginateForAdmin($request),
            'statuses' => MapMarkerStatus::cases(),
            'uzgolonlar' => Uzgolon::orderBy('name')->get(),
            'filters' => $request->only(['search', 'status', 'uzgolon_id']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', MapMarker::class);

        return view('admin.map-markers.form', $this->formData(new MapMarker));
    }

    public function store(StoreMapMarkerRequest $request): RedirectResponse
    {
        $marker = $this->mapMarkerService->create($request->validated());

        return redirect()->route('admin.map-markers.index')
            ->with('status', "\"{$marker->title}\" muvaffaqiyatli qo'shildi.");
    }

    public function show(MapMarker $mapMarker): View
    {
        $this->authorize('view', $mapMarker);

        $mapMarker->load('uzgolon');

        return view('admin.map-markers.show', [
            'title' => $mapMarker->title,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Xarita markerlari', 'url' => route('admin.map-markers.index')],
                ['label' => $mapMarker->title],
            ],
            'mapMarker' => $mapMarker,
        ]);
    }

    public function edit(MapMarker $mapMarker): View
    {
        $this->authorize('update', $mapMarker);

        return view('admin.map-markers.form', $this->formData($mapMarker));
    }

    public function update(UpdateMapMarkerRequest $request, MapMarker $mapMarker): RedirectResponse
    {
        $mapMarker = $this->mapMarkerService->update($mapMarker, $request->validated());

        return redirect()->route('admin.map-markers.index')->with('status', "\"{$mapMarker->title}\" yangilandi.");
    }

    public function destroy(MapMarker $mapMarker): RedirectResponse
    {
        $this->authorize('delete', $mapMarker);

        $title = $mapMarker->title;
        $this->mapMarkerService->delete($mapMarker);

        return redirect()->route('admin.map-markers.index')->with('status', "\"{$title}\" o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(MapMarker $mapMarker): array
    {
        return [
            'title' => $mapMarker->exists ? "Tahrirlash — {$mapMarker->title}" : 'Yangi marker',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Xarita markerlari', 'url' => route('admin.map-markers.index')],
                ['label' => $mapMarker->exists ? 'Tahrirlash' : 'Yangi'],
            ],
            'mapMarker' => $mapMarker,
            'statuses' => MapMarkerStatus::cases(),
            'types' => MapMarkerType::cases(),
            'uzgolonlar' => Uzgolon::orderBy('name')->get(),
        ];
    }
}
