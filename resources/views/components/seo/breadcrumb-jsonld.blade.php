@props(['items' => []])

{{--
    XAVFSIZLIK: json_encode() standart holatda `<`, `>`, `&`, `'` belgilarini
    escape qilmaydi — agar breadcrumb label (masalan Blog sarlavhasi) foydalanuvchi
    kontenti bo'lsa, "</script><script>..." orqali script'dan chiqib ketish mumkin
    edi. JSON_HEX_* flaglari bu belgilarni \uXXXX ko'rinishiga aylantiradi, script
    context'dan chiqib ketish texnik jihatdan imkonsiz bo'ladi.
--}}
@if (count($items))
    <script type="application/ld+json">
        {{-- '@context' → @@context: Blade'ning @context direktivasi bilan
        to'qnashmasligi uchun escape qilingan (pastdagi article-jsonld'dagi
        bir xil bug). --}}
        {!! json_encode([
            '@@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($items)->values()->map(fn ($crumb, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['label'],
                'item' => $crumb['url'] ?? url()->current(),
            ])->all(),
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
    </script>
@endif
