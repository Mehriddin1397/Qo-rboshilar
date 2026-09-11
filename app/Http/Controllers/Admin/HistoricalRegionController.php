<?php

namespace App\Http\Controllers\Admin;

use App\Enums\HistoricalAccuracyStatus;
use App\Enums\HistoricalRegionStatus;
use App\Enums\HistoricalRegionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHistoricalRegionRequest;
use App\Http\Requests\UpdateHistoricalRegionRequest;
use App\Models\HistoricalRegion;
use App\Models\Period;
use App\Models\Region;
use App\Models\SourceReference;
use App\Services\HistoricalRegionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class HistoricalRegionController extends Controller
{
    public function __construct(private readonly HistoricalRegionService $historicalRegionService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', HistoricalRegion::class);

        return view('admin.historical-regions.index', [
            'title' => 'Tarixiy hududlar',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Tarixiy hududlar'],
            ],
            'items' => $this->historicalRegionService->paginateForAdmin($request),
            'statuses' => HistoricalRegionStatus::cases(),
            'periods' => Period::orderBy('start_year')->get(),
            'regions' => Region::orderBy('name')->get(),
            'filters' => $request->only(['search', 'status', 'period_id', 'region_id', 'featured']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', HistoricalRegion::class);

        return view('admin.historical-regions.form', $this->formData(new HistoricalRegion));
    }

    public function store(StoreHistoricalRegionRequest $request): RedirectResponse
    {
        $historicalRegion = $this->historicalRegionService->create($request->validated());

        return redirect()->route('admin.historical-regions.index')
            ->with('status', "\"{$historicalRegion->name}\" muvaffaqiyatli qo'shildi.");
    }

    public function show(HistoricalRegion $historicalRegion): View
    {
        $this->authorize('view', $historicalRegion);

        $historicalRegion->load(['period', 'region', 'historicalMapLayers', 'sourceReferences']);

        return view('admin.historical-regions.show', [
            'title' => $historicalRegion->name,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Tarixiy hududlar', 'url' => route('admin.historical-regions.index')],
                ['label' => $historicalRegion->name],
            ],
            'historicalRegion' => $historicalRegion,
        ]);
    }

    public function edit(HistoricalRegion $historicalRegion): View
    {
        $this->authorize('update', $historicalRegion);

        $historicalRegion->load('sourceReferences');

        return view('admin.historical-regions.form', $this->formData($historicalRegion));
    }

    public function update(UpdateHistoricalRegionRequest $request, HistoricalRegion $historicalRegion): RedirectResponse
    {
        $historicalRegion = $this->historicalRegionService->update($historicalRegion, $request->validated());

        return redirect()->route('admin.historical-regions.index')
            ->with('status', "\"{$historicalRegion->name}\" yangilandi.");
    }

    public function destroy(HistoricalRegion $historicalRegion): RedirectResponse
    {
        $this->authorize('delete', $historicalRegion);

        $name = $historicalRegion->name;
        $this->historicalRegionService->delete($historicalRegion);

        return redirect()->route('admin.historical-regions.index')->with('status', "\"{$name}\" o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(HistoricalRegion $historicalRegion): array
    {
        return [
            'title' => $historicalRegion->exists ? "Tahrirlash — {$historicalRegion->name}" : 'Yangi tarixiy hudud',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Tarixiy hududlar', 'url' => route('admin.historical-regions.index')],
                ['label' => $historicalRegion->exists ? 'Tahrirlash' : 'Yangi'],
            ],
            'historicalRegion' => $historicalRegion,
            'statuses' => HistoricalRegionStatus::cases(),
            'regionTypes' => HistoricalRegionType::cases(),
            'accuracyStatuses' => HistoricalAccuracyStatus::cases(),
            'periods' => Period::orderBy('start_year')->get(),
            'regions' => Region::orderBy('name')->get(),
            'sourceReferences' => SourceReference::orderBy('title')->get(),
        ];
    }
}
