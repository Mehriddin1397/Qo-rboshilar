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
                'title' => "Bog'lanish — Qo'rboshilar.uz tahririyati bilan aloqa",
                'description' => "Qo'rboshilar.uz loyihasi tahririyati bilan bog'lanish, tarixiy materiallar va manbalarni taqdim etish hamda takliflar yuborish uchun aloqa sahifasi.",
                'canonical' => route('contact'),
            ],
        ]);
    }
}
