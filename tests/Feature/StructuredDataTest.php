<?php

namespace Tests\Feature;

use App\Models\Blog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Production QA §11: `'@context'` so'zi Blade'ning @context direktivasi
 * (Laravel Context feature) bilan to'qnashib, JSON-LD'ni butunlay buzib
 * yuborgan edi (audit paytida topilgan bug — @@context bilan tuzatildi).
 * Bu test JSON-LD har doim valid JSON bo'lib qolishini kafolatlaydi.
 */
class StructuredDataTest extends TestCase
{
    use RefreshDatabase;

    private function extractJsonLdBlocks(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        return $matches[1] ?? [];
    }

    public function test_homepage_website_json_ld_is_valid(): void
    {
        $response = $this->get('/');
        $blocks = $this->extractJsonLdBlocks($response->getContent());

        $this->assertNotEmpty($blocks);

        foreach ($blocks as $block) {
            $decoded = json_decode($block, true);
            $this->assertNotNull($decoded, "JSON-LD block failed to decode: {$block}");
            $this->assertSame('https://schema.org', $decoded['@context']);
        }
    }

    public function test_blog_detail_article_and_breadcrumb_json_ld_are_valid(): void
    {
        $blog = Blog::factory()->approved()->create(['title' => 'JSON-LD Test Blog']);

        $response = $this->get(route('bloglar.show', $blog));
        $blocks = $this->extractJsonLdBlocks($response->getContent());

        $this->assertGreaterThanOrEqual(3, count($blocks));

        $types = [];
        foreach ($blocks as $block) {
            $decoded = json_decode($block, true);
            $this->assertNotNull($decoded, "JSON-LD block failed to decode: {$block}");
            $this->assertSame('https://schema.org', $decoded['@context']);
            $types[] = $decoded['@type'];
        }

        $this->assertContains('Article', $types);
        $this->assertContains('BreadcrumbList', $types);
    }
}
