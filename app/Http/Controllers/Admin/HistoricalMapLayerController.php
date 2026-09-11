<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HistoricalAccuracyStatus;
use App\Enums\HistoricalMapLayerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHistoricalMapLayerRequest;
use App\Http\Requests\UpdateHistoricalMapLayerRequest;
use App\Models\HistoricalMapLayer;
use App\Models\HistoricalRegion;
use App\Models\Period;
use App\Models\SourceReference;
use App\Services\HistoricalMapLayerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HistoricalMapLayerController extends Controller
{
    public function __construct(private readonly HistoricalMapLayerService $historicalMapLayerService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', HistoricalMapLayer::class);

        return view('admin.historical-map-layers.index', [
            'title' => 'Xarita qatlamlari',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Xarita qatlamlari'],
            ],
            'items' => $this->historicalMapLayerService->paginateForAdmin($request),
            'statuses' => HistoricalMapLayerStatus::cases(),
            'periods' => Period::orderBy('start_year')->get(),
            'historicalRegions' => HistoricalRegion::orderBy('name')->get(),
            'filters' => $request->only(['search', 'status', 'period_id', 'historical_region_id']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', HistoricalMapLayer::class);

        return view('admin.historical-map-layers.form', $this->formData(new HistoricalMapLayer));
    }

    public function store(StoreHistoricalMapLayerRequest $request): RedirectResponse
    {
        $layer = $this->historicalMapLayerService->create($request->validated());

        $this->handleUploads($request, $layer);

        return redirect()->route('admin.historical-map-layers.index')
            ->with('status', "\"{$layer->title}\" muvaffaqiyatli qo'shildi.");
    }

    public function show(HistoricalMapLayer $historicalMapLayer): View
    {
        $this->authorize('view', $historicalMapLayer);

        $historicalMapLayer->load(['period', 'historicalRegion', 'sourceReferences']);

        return view('admin.historical-map-layers.show', [
            'title' => $historicalMapLayer->title,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Xarita qatlamlari', 'url' => route('admin.historical-map-layers.index')],
                ['label' => $historicalMapLayer->title],
            ],
            'layer' => $historicalMapLayer,
        ]);
    }

    public function edit(HistoricalMapLayer $historicalMapLayer): View
    {
        $this->authorize('update', $historicalMapLayer);

        $historicalMapLayer->load(['sourceReferences']);

        return view('admin.historical-map-layers.form', $this->formData($historicalMapLayer));
    }

    public function update(UpdateHistoricalMapLayerRequest $request, HistoricalMapLayer $historicalMapLayer): RedirectResponse
    {
        $layer = $this->historicalMapLayerService->update($historicalMapLayer, $request->validated());

        $this->handleUploads($request, $layer);

        return redirect()->route('admin.historical-map-layers.index')->with('status', "\"{$layer->title}\" yangilandi.");
    }

    public function destroy(HistoricalMapLayer $historicalMapLayer): RedirectResponse
    {
        $this->authorize('delete', $historicalMapLayer);

        $title = $historicalMapLayer->title;
        $this->historicalMapLayerService->delete($historicalMapLayer);

        return redirect()->route('admin.historical-map-layers.index')->with('status', "\"{$title}\" o'chirildi.");
    }

    private function handleUploads(Request $request, HistoricalMapLayer $layer): void
    {
        if ($request->hasFile('image')) {
            if ($layer->image_path) {
                Storage::disk('public')->delete($layer->image_path);
            }

            $layer->update(['image_path' => $request->file('image')->store('historical-map-layers', 'public')]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(HistoricalMapLayer $layer): array
    {
        return [
            'title' => $layer->exists ? "Tahrirlash — {$layer->title}" : 'Yangi xarita qatlami',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Xarita qatlamlari', 'url' => route('admin.historical-map-layers.index')],
                ['label' => $layer->exists ? 'Tahrirlash' : 'Yangi'],
            ],
            'layer' => $layer,
            'statuses' => HistoricalMapLayerStatus::cases(),
            'accuracyStatuses' => HistoricalAccuracyStatus::cases(),
            'periods' => Period::orderBy('start_year')->get(),
            'historicalRegions' => HistoricalRegion::orderBy('name')->get(),
            'sourceReferences' => SourceReference::orderBy('title')->get(),
        ];
    }
}
