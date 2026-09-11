<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RegionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegionRequest;
use App\Http\Requests\UpdateRegionRequest;
use App\Models\Region;
use App\Services\RegionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function __construct(private readonly RegionService $regionService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Region::class);

        return view('admin.regions.index', [
            'title' => 'Hududlar',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Hududlar'],
            ],
            'items' => $this->regionService->paginateForAdmin($request),
            'statuses' => RegionStatus::cases(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Region::class);

        return view('admin.regions.form', $this->formData(new Region));
    }

    public function store(StoreRegionRequest $request): RedirectResponse
    {
        $region = $this->regionService->create($request->validated());

        return redirect()->route('admin.regions.index')
            ->with('status', "\"{$region->name}\" muvaffaqiyatli qo'shildi.");
    }

    public function show(Region $region): View
    {
        $this->authorize('view', $region);

        $region->loadCount(['qorboshilar', 'uzgolonlar']);
        $region->load(['qorboshilar' => fn ($q) => $q->latest()->limit(10), 'uzgolonlar' => fn ($q) => $q->latest()->limit(10)]);

        return view('admin.regions.show', [
            'title' => $region->name,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Hududlar', 'url' => route('admin.regions.index')],
                ['label' => $region->name],
            ],
            'region' => $region,
        ]);
    }

    public function edit(Region $region): View
    {
        $this->authorize('update', $region);

        return view('admin.regions.form', $this->formData($region));
    }

    public function update(UpdateRegionRequest $request, Region $region): RedirectResponse
    {
        $region = $this->regionService->update($region, $request->validated());

        return redirect()->route('admin.regions.index')->with('status', "\"{$region->name}\" yangilandi.");
    }

    public function destroy(Region $region): RedirectResponse
    {
        $this->authorize('delete', $region);

        $qorboshilarCount = $region->qorboshilar()->count();
        $uzgolonlarCount = $region->uzgolonlar()->count();

        if ($qorboshilarCount > 0 || $uzgolonlarCount > 0) {
            return redirect()->route('admin.regions.index')->with('error',
                "\"{$region->name}\" hududini o'chirib bo'lmaydi: unga {$qorboshilarCount} ta qo'rboshi va "
                ."{$uzgolonlarCount} ta qo'zg'olon bog'langan. Avval ularni boshqa hududga o'tkazing yoki bog'lanishini bekor qiling."
            );
        }

        $name = $region->name;
        $this->regionService->delete($region);

        return redirect()->route('admin.regions.index')->with('status', "\"{$name}\" o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Region $region): array
    {
        return [
            'title' => $region->exists ? "Tahrirlash — {$region->name}" : 'Yangi hudud',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Hududlar', 'url' => route('admin.regions.index')],
                ['label' => $region->exists ? 'Tahrirlash' : 'Yangi'],
            ],
            'region' => $region,
            'statuses' => RegionStatus::cases(),
        ];
    }
}
