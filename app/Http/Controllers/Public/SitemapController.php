<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\SitemapService;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __construct(private readonly SitemapService $sitemapService)
    {
    }

    public function index(): Response
    {
        $xml = view('sitemap', ['urls' => $this->sitemapService->urls()])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
