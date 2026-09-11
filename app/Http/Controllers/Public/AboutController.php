<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    public function index(): View
    {
        return view('pages.about', [
            'seo' => [
                'title' => "Biz haqimizda — Qo'rboshilar.uz",
                'description' => "Qo'rboshilar.uz loyihasi haqida — maqsad va tarkib.",
                'canonical' => route('about'),
            ],
        ]);
    }
}
