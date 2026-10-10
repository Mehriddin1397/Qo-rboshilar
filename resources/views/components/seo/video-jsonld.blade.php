@props(['name', 'description' => null, 'thumbnailUrl' => null, 'uploadDate' => null, 'embedUrl' => null, 'url'])

<script type="application/ld+json">
    {!! json_encode(array_filter([
        '@@context' => 'https://schema.org',
        '@type' => 'VideoObject',
        'name' => $name,
        'description' => $description,
        'thumbnailUrl' => $thumbnailUrl ? [$thumbnailUrl] : null,
        'uploadDate' => $uploadDate?->toAtomString(),
        'embedUrl' => $embedUrl,
        'url' => $url,
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
    ]), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) !!}
</script>
