<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SourceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSourceReferenceRequest;
use App\Http\Requests\UpdateSourceReferenceRequest;
use App\Models\Literature;
use App\Models\SourceReference;
use App\Services\SourceReferenceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SourceReferenceController extends Controller
{
    public function __construct(private readonly SourceReferenceService $sourceReferenceService)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SourceReference::class);

        return view('admin.source-references.index', [
            'title' => 'Manbalar',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Manbalar'],
            ],
            'items' => $this->sourceReferenceService->paginateForAdmin($request),
            'sourceTypes' => SourceType::cases(),
            'filters' => $request->only(['search', 'source_type']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', SourceReference::class);

        return view('admin.source-references.form', $this->formData(new SourceReference));
    }

    public function store(StoreSourceReferenceRequest $request): RedirectResponse
    {
        $sourceReference = $this->sourceReferenceService->create($request->validated());

        return redirect()->route('admin.source-references.index')
            ->with('status', "\"{$sourceReference->title}\" muvaffaqiyatli qo'shildi.");
    }

    public function show(SourceReference $sourceReference): View
    {
        $this->authorize('view', $sourceReference);

        $sourceReference->load('literature');

        return view('admin.source-references.show', [
            'title' => $sourceReference->title,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Manbalar', 'url' => route('admin.source-references.index')],
                ['label' => $sourceReference->title],
            ],
            'sourceReference' => $sourceReference,
            'relatedCounts' => $this->sourceReferenceService->relatedCounts($sourceReference),
        ]);
    }

    public function edit(SourceReference $sourceReference): View
    {
        $this->authorize('update', $sourceReference);

        return view('admin.source-references.form', $this->formData($sourceReference));
    }

    public function update(UpdateSourceReferenceRequest $request, SourceReference $sourceReference): RedirectResponse
    {
        $sourceReference = $this->sourceReferenceService->update($sourceReference, $request->validated());

        return redirect()->route('admin.source-references.index')->with('status', "\"{$sourceReference->title}\" yangilandi.");
    }

    public function destroy(SourceReference $sourceReference): RedirectResponse
    {
        $this->authorize('delete', $sourceReference);

        $related = array_filter($this->sourceReferenceService->relatedCounts($sourceReference));

        if (! empty($related)) {
            $summary = collect($related)->map(fn ($count, $label) => "{$count} ta {$label}")->implode(', ');

            return redirect()->route('admin.source-references.index')->with('error',
                "\"{$sourceReference->title}\" manbasini o'chirib bo'lmaydi: unga {$summary} bog'langan. ".
                "Avval o'sha yozuvlardan bu manbani olib tashlang."
            );
        }

        $title = $sourceReference->title;
        $this->sourceReferenceService->delete($sourceReference);

        return redirect()->route('admin.source-references.index')->with('status', "\"{$title}\" o'chirildi.");
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(SourceReference $sourceReference): array
    {
        return [
            'title' => $sourceReference->exists ? "Tahrirlash — {$sourceReference->title}" : 'Yangi manba',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Manbalar', 'url' => route('admin.source-references.index')],
                ['label' => $sourceReference->exists ? 'Tahrirlash' : 'Yangi'],
            ],
            'sourceReference' => $sourceReference,
            'sourceTypes' => SourceType::cases(),
            'literatures' => Literature::orderBy('title')->get(),
        ];
    }
}
