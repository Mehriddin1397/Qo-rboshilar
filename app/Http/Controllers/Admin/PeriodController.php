<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePeriodRequest;
use App\Http\Requests\UpdatePeriodRequest;
use App\Models\Period;
use App\Services\PeriodService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    public function __construct(private readonly PeriodService $periodService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Period::class);

        return view('admin.periods.index', [
            'title' => 'Davrlar',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Davrlar'],
            ],
            'items' => $this->periodService->paginateForAdmin($request),
            'filters' => $request->only(['search']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Period::class);

        return view('admin.periods.form', $this->formData(new Period));
    }

    public function store(StorePeriodRequest $request): RedirectResponse
    {
        $period = $this->periodService->create($request->validated());

        return redirect()->route('admin.periods.index')
            ->with('status', "\"{$period->name}\" muvaffaqiyatli qo'shildi.");
    }

    public function show(Period $period): View
    {
        $this->authorize('view', $period);

        $period->loadCount(['uzgolonlar', 'historicalRegions', 'historicalMapLayers', 'timelineEvents']);
        $period->load([
            'uzgolonlar' => fn ($q) => $q->latest()->limit(10),
            'historicalRegions' => fn ($q) => $q->latest()->limit(10),
        ]);

        return view('admin.periods.show', [
            'title' => $period->name,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Davrlar', 'url' => route('admin.periods.index')],
                ['label' => $period->name],
            ],
            'period' => $period,
        ]);
    }

    public function edit(Period $period): View
    {
        $this->authorize('update', $period);

        return view('admin.periods.form', $this->formData($period));
    }

    public function update(UpdatePeriodRequest $request, Period $period): RedirectResponse
    {
        $period = $this->periodService->update($period, $request->validated());

        return redirect()->route('admin.periods.index')->with('status', "\"{$period->name}\" yangilandi.");
    }

    public function destroy(Period $period): RedirectResponse
    {
        $this->authorize('delete', $period);

        $counts = [
            "qo'zg'olon" => $period->uzgolonlar()->count(),
            'tarixiy hudud' => $period->historicalRegions()->count(),
            'xarita qatlami' => $period->historicalMapLayers()->count(),
            'xronologiya voqeasi' => $period->timelineEvents()->count(),
        ];

        $related = array_filter($counts);

        if (! empty($related)) {
            $summary = collect($related)->map(fn ($count, $label) => "{$count} ta {$label}")->implode(', ');

            return redirect()->route('admin.periods.index')->with('error',
                "\"{$period->name}\" davrini o'chirib bo'lmaydi: unga {$summary} bog'langan. ".
                "Avval ularni boshqa davrga o'tkazing yoki bog'lanishini bekor qiling."
            );
        }

        $name = $period->name;
        $this->periodService->delete($period);

        return redirect()->route('admin.periods.index')->with('status', "\"{$name}\" o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Period $period): array
    {
        return [
            'title' => $period->exists ? "Tahrirlash — {$period->name}" : 'Yangi davr',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Davrlar', 'url' => route('admin.periods.index')],
                ['label' => $period->exists ? 'Tahrirlash' : 'Yangi'],
            ],
            'period' => $period,
        ];
    }
}
