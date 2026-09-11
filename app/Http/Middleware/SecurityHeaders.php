<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Production QA §7: standart himoya header'lari — hech qanday yangi dependency
 * talab qilmaydi. Content-Security-Policy ataylab QO'SHILMADI — Alpine.js va
 * MapLibre GL bilan ishlaydigan qat'iy CSP yozish alohida sinovni talab qiladi
 * (noto'g'ri CSP mavjud funksiyalarni buzishi mumkin); bu keyingi, alohida
 * ehtiyotkorlik bilan qilinadigan ish sifatida qoldirilgan (DEPLOYMENT.md'da
 * qayd etilgan).
 */
class SecurityHeaders
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
