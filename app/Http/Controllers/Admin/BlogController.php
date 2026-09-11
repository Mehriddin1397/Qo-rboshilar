<?php

namespace App\Http\Controllers\Admin;

use App\Enums\BlogStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Models\Blog;
use App\Models\Literature;
use App\Models\Qorboshi;
use App\Models\SourceReference;
use App\Models\User;
use App\Models\Uzgolon;
use App\Models\Video;
use App\Services\BlogMediaService;
use App\Services\BlogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        private readonly BlogService $blogService,
        private readonly BlogMediaService $mediaService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Blog::class);

        return view('admin.bloglar.index', [
            'title' => 'Bloglar',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Bloglar'],
            ],
            'items' => $this->blogService->paginateForAdmin($request),
            'statuses' => BlogStatus::cases(),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Blog::class);

        return view('admin.bloglar.form', $this->formData(new Blog));
    }

    public function store(StoreBlogRequest $request): RedirectResponse
    {
        $blog = $this->blogService->createAsAdmin($request->validated());

        $this->handleUploads($request, $blog);

        return redirect()->route('admin.bloglar.index')->with('status', "\"{$blog->title}\" muvaffaqiyatli qo'shildi.");
    }

    public function show(Blog $blog): View
    {
        $this->authorize('view', $blog);

        $blog->load(['author', 'qorboshilar', 'uzgolonlar', 'literatures', 'videos', 'sourceReferences']);

        return view('admin.bloglar.show', [
            'title' => $blog->title,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Bloglar', 'url' => route('admin.bloglar.index')],
                ['label' => $blog->title],
            ],
            'blog' => $blog,
        ]);
    }

    public function edit(Blog $blog): View
    {
        $this->authorize('update', $blog);

        $blog->load(['qorboshilar', 'uzgolonlar', 'literatures', 'videos']);

        return view('admin.bloglar.form', $this->formData($blog));
    }

    public function update(UpdateBlogRequest $request, Blog $blog): RedirectResponse
    {
        $blog = $this->blogService->updateAsAdmin($blog, $request->validated());

        $this->handleUploads($request, $blog);

        return redirect()->route('admin.bloglar.index')->with('status', "\"{$blog->title}\" yangilandi.");
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        $this->authorize('delete', $blog);

        $title = $blog->title;
        $this->blogService->delete($blog);

        return redirect()->route('admin.bloglar.index')->with('status', "\"{$title}\" o'chirildi.");
    }

    public function approve(Blog $blog): RedirectResponse
    {
        $this->authorize('approve', $blog);

        $this->blogService->approve($blog);

        return back()->with('status', "\"{$blog->title}\" tasdiqlandi va nashr etildi.");
    }

    public function reject(Blog $blog): RedirectResponse
    {
        $this->authorize('reject', $blog);

        $this->blogService->reject($blog);

        return back()->with('status', "\"{$blog->title}\" rad etildi.");
    }

    private function handleUploads(Request $request, Blog $blog): void
    {
        if ($request->hasFile('cover')) {
            $blog->update(['cover_path' => $this->mediaService->storeCover($blog, $request->file('cover'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(Blog $blog): array
    {
        return [
            'title' => $blog->exists ? "Tahrirlash — {$blog->title}" : "Yangi blog",
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Bloglar', 'url' => route('admin.bloglar.index')],
                ['label' => $blog->exists ? 'Tahrirlash' : 'Yangi'],
            ],
            'blog' => $blog,
            'statuses' => BlogStatus::cases(),
            'authors' => User::orderBy('name')->get(),
            'qorboshilar' => Qorboshi::orderBy('full_name')->get(),
            'uzgolonlar' => Uzgolon::orderBy('name')->get(),
            'literatures' => Literature::orderBy('title')->get(),
            'videos' => Video::orderBy('title')->get(),
            'sourceReferences' => SourceReference::orderBy('title')->get(),
        ];
    }
}
