<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Literature;
use App\Models\Qorboshi;
use App\Models\Uzgolon;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Production QA §11: Technical SEO & Structured Data (JSON-LD, Sitemap) testlari.
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

    public function test_detail_pages_render_appropriate_schema_types(): void
    {
        $qorboshi = Qorboshi::factory()->published()->create(['full_name' => 'Madaminbek']);
        $resp = $this->get(route('qorboshilar.show', $qorboshi));
        $resp->assertOk();
        $blocks = $this->extractJsonLdBlocks($resp->getContent());
        $types = array_map(fn($b) => json_decode($b, true)['@type'] ?? null, $blocks);
        $this->assertContains('Person', $types);

        $uzgolon = Uzgolon::factory()->published()->create(['name' => 'Fargona Qozgoloni']);
        $resp = $this->get(route('qozgolonlar.show', $uzgolon));
        $resp->assertOk();
        $blocks = $this->extractJsonLdBlocks($resp->getContent());
        $types = array_map(fn($b) => json_decode($b, true)['@type'] ?? null, $blocks);
        $this->assertContains('Event', $types);

        $book = Literature::factory()->published()->create(['title' => 'Ozodlik jangi']);
        $resp = $this->get(route('adabiyotlar.show', $book));
        $resp->assertOk();
        $blocks = $this->extractJsonLdBlocks($resp->getContent());
        $types = array_map(fn($b) => json_decode($b, true)['@type'] ?? null, $blocks);
        $this->assertContains('Book', $types);

        $video = Video::factory()->published()->create(['title' => 'Tarixiy Hujjatli Film']);
        $resp = $this->get(route('videolar.show', $video));
        $resp->assertOk();
        $blocks = $this->extractJsonLdBlocks($resp->getContent());
        $types = array_map(fn($b) => json_decode($b, true)['@type'] ?? null, $blocks);
        $this->assertContains('VideoObject', $types);
    }

    public function test_sitemap_returns_valid_xml_with_all_sections(): void
    {
        config(['app.url' => 'https://qorboshilar.uz']);

        $response = $this->get(route('sitemap'));
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');

        $content = $response->getContent();
        $this->assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $content);
        $this->assertStringContainsString('<loc>https://qorboshilar.uz/</loc>', $content);
        $this->assertStringContainsString('<changefreq>', $content);
        $this->assertStringContainsString('<priority>', $content);
        $this->assertStringContainsString('<lastmod>', $content);
    }
}
