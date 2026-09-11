<?php

namespace App\Http\Controllers\Public;

use App\Enums\ContentType;
use App\Http\Controllers\Controller;
use App\Services\SearchService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SearchController extends Controller
{
    public function __construct(private readonly SearchService $searchService)
    {
    }

    public function index(Request $request): View
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', 'string', Rule::in(array_column(ContentType::cases(), 'value'))],
        ]);

        $query = trim($data['q'] ?? '');
        $type = $data['type'] ?? null;

        return view('search.index', [
            'query' => $query,
            'type' => $type,
            'types' => ContentType::cases(),
            'results' => $query !== '' ? $this->searchService->search($query, $type) : null,
            'seo' => [
                // §17: $seo['title'] @yield orqali escape qilinmasdan chiqadi (butun saytda
                // shu naqsh), shuning uchun so'rovdan kelgan $query bu yerda qo'lda escape
                // qilinadi — aks holda reflected XSS bo'lardi.
                'title' => ($query !== '' ? '"'.e($query).'" bo\'yicha qidiruv' : 'Qidiruv')." — Qo'rboshilar.uz",
                'canonical' => route('search', $request->only(['q', 'type'])),
            ],
        ]);
    }
}
