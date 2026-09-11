<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        return view('pages.contact', [
            'seo' => [
                'title' => "Bog'lanish — Qo'rboshilar.uz",
                'description' => "Qo'rboshilar.uz loyihasi bilan bog'lanish yo'llari.",
                'canonical' => route('contact'),
            ],
        ]);
    }
}
