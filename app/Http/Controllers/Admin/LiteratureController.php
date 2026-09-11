<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LiteratureStatus;
use App\Enums\LiteratureType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLiteratureRequest;
use App\Http\Requests\UpdateLiteratureRequest;
use App\Models\Literature;
use App\Models\Qorboshi;
use App\Models\Uzgolon;
use App\Services\LiteratureMediaService;
use App\Services\LiteratureService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LiteratureController extends Controller
{
    public function __construct(
        private readonly LiteratureService $literatureService,
        private readonly LiteratureMediaService $mediaService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Literature::class);

        return view('admin.adabiyotlar.index', [
            'title' => 'Adabiyotlar',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Adabiyotlar'],
            ],
            'items' => $this->literatureService->paginateForAdmin($request),
            'types' => LiteratureType::cases(),
            'statuses' => LiteratureStatus::cases(),
            'filters' => $request->only(['search', 'type', 'status', 'featured']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Literature::class);

        return view('admin.adabiyotlar.form', $this->formData(new Literature));
    }

    public function store(StoreLiteratureRequest $request): RedirectResponse
    {
        $literature = $this->literatureService->create($request->validated());

        $this->handleUploads($request, $literature);

        return redirect()->route('admin.adabiyotlar.index')->with('status', "\"{$literature->title}\" muvaffaqiyatli qo'shildi.");
    }

    public function show(Literature $adabiyot): View
    {
        $this->authorize('view', $adabiyot);

        $adabiyot->load(['qorboshilar', 'uzgolonlar', 'videos', 'sourceReferences']);

        return view('admin.adabiyotlar.show', [
            'title' => $adabiyot->title,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Adabiyotlar', 'url' => route('admin.adabiyotlar.index')],
                ['label' => $adabiyot->title],
            ],
            'literature' => $adabiyot,
        ]);
    }

    public function edit(Literature $adabiyot): View
    {
        $this->authorize('update', $adabiyot);

        $adabiyot->load(['qorboshilar', 'uzgolonlar']);

        return view('admin.adabiyotlar.form', $this->formData($adabiyot));
    }

    public function update(UpdateLiteratureRequest $request, Literature $adabiyot): RedirectResponse
    {
        $adabiyot = $this->literatureService->update($adabiyot, $request->validated());

        $this->handleUploads($request, $adabiyot);

        return redirect()->route('admin.adabiyotlar.index')->with('status', "\"{$adabiyot->title}\" yangilandi.");
    }

    public function destroy(Literature $adabiyot): RedirectResponse
    {
        $this->authorize('delete', $adabiyot);

        $title = $adabiyot->title;
        $this->literatureService->delete($adabiyot);

        return redirect()->route('admin.adabiyotlar.index')->with('status', "\"{$title}\" o'chirildi.");
    }

    private function handleUploads(Request $request, Literature $literature): void
    {
        if ($request->hasFile('cover')) {
            $literature->update(['cover_path' => $this->mediaService->storeCover($literature, $request->file('cover'))]);
        }

        if ($request->hasFile('file')) {
            $literature->update(['file_path' => $this->mediaService->storeFile($literature, $request->file('file'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Literature $literature): array
    {
        return [
            'title' => $literature->exists ? "Tahrirlash — {$literature->title}" : "Yangi adabiyot",
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Adabiyotlar', 'url' => route('admin.adabiyotlar.index')],
                ['label' => $literature->exists ? 'Tahrirlash' : 'Yangi'],
            ],
            'literature' => $literature,
            'types' => LiteratureType::cases(),
            'statuses' => LiteratureStatus::cases(),
            'qorboshilar' => Qorboshi::orderBy('full_name')->get(),
            'uzgolonlar' => Uzgolon::orderBy('name')->get(),
        ];
    }
}
