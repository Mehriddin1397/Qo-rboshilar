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
                'title' => "Biz haqimizda — Qo'rboshilar.uz: Turkiston tarixi raqamli platformasi",
                'description' => "Qo'rboshilar.uz loyihasining maqsadi, vazifalari va ilmiy-tarixiy asoslari. Turkiston milliy ozodlik harakatini o'rganish bo'yicha raqamli ensiklopediya.",
                'canonical' => route('about'),
            ],
        ]);
    }
}
