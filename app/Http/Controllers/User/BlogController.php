<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMyBlogRequest;
use App\Http\Requests\UpdateMyBlogRequest;
use App\Models\Blog;
use App\Models\Qorboshi;
use App\Models\Uzgolon;
use App\Services\BlogMediaService;
use App\Services\BlogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * "Mening bloglarim" — oddiy ro'yxatdan o'tgan foydalanuvchi o'z bloglarini shu yerda
 * yaratadi/tahrirlaydi/submit qiladi. Admin moderatsiyasi (approve/reject) —
 * Admin\BlogController'da, chunki bu alohida rol (Editor/Admin)ga tegishli amal.
 */
class BlogController extends Controller
{
    public function __construct(
        private readonly BlogService $blogService,
        private readonly BlogMediaService $mediaService,
    ) {
    }

    public function index(Request $request): View
    {
        return view('bloglarim.index', [
            'title' => 'Mening bloglarim',
            'items' => $this->blogService->paginateForAuthor($request->user()),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Blog::class);

        return view('bloglarim.form', $this->formData(new Blog));
    }

    public function store(StoreMyBlogRequest $request): RedirectResponse
    {
        $blog = $this->blogService->createAsAuthor($request->user(), $request->validated());

        $this->handleUploads($request, $blog);

        return redirect()->route('bloglarim.index')->with('status', "\"{$blog->title}\" qoralama sifatida saqlandi.");
    }

    public function edit(Blog $blogim): View
    {
        $this->authorize('update', $blogim);

        $blogim->load(['qorboshilar', 'uzgolonlar']);

        return view('bloglarim.form', $this->formData($blogim));
    }

    public function update(UpdateMyBlogRequest $request, Blog $blogim): RedirectResponse
    {
        $this->authorize('update', $blogim);

        $blogim = $this->blogService->updateAsAuthor($blogim, $request->validated());

        $this->handleUploads($request, $blogim);

        return redirect()->route('bloglarim.index')->with('status', "\"{$blogim->title}\" yangilandi.");
    }

    public function submit(Blog $blogim): RedirectResponse
    {
        $this->authorize('submit', $blogim);

        $this->blogService->submit($blogim);

        return redirect()->route('bloglarim.index')->with('status', "\"{$blogim->title}\" moderatsiyaga yuborildi.");
    }

    public function destroy(Blog $blogim): RedirectResponse
    {
        $this->authorize('delete', $blogim);

        $title = $blogim->title;
        $this->blogService->delete($blogim);

        return redirect()->route('bloglarim.index')->with('status', "\"{$title}\" o'chirildi.");
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
            'blog' => $blog,
            'qorboshilar' => Qorboshi::orderBy('full_name')->get(),
            'uzgolonlar' => Uzgolon::orderBy('name')->get(),
        ];
    }
}
