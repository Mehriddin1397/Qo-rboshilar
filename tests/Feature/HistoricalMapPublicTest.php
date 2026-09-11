<?php

namespace Tests\Feature;

use App\Models\HistoricalMapLayer;
use App\Models\HistoricalRegion;
use App\Models\Period;
use App\Models\SourceReference;
use App\Models\Uzgolon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Faza 13 §48-52: public /xarita sahifasining tarixiy qatlam integratsiyasi —
 * published-only ko'rinish, davr filtri, XSS, IDOR, N+1, Qo'zg'olon regressiyasi.
 */
class HistoricalMapPublicTest extends TestCase
{
    use RefreshDatabase;

    private function polygonGeoJson(): string
    {
        return json_encode([
            'type' => 'Polygon',
            'coordinates' => [[[64, 41], [65, 41], [65, 42], [64, 41]]],
        ]);
    }

    public function test_map_page_loads_for_guest(): void
    {
        $this->get('/xarita')->assertOk();
    }

    public function test_published_historical_region_with_geojson_is_visible(): void
    {
        $source = SourceReference::factory()->create();
        $region = HistoricalRegion::factory()->published()->create([
            'name' => 'Ochiq Hudud',
            'geojson' => json_decode($this->polygonGeoJson(), true),
        ]);
        $region->sourceReferences()->attach($source->id);

        $response = $this->get('/xarita');

        $response->assertOk();
        $response->assertViewHas('historicalRegions', function ($geojson) {
            return collect($geojson['features'])->contains(fn ($f) => $f['properties']['name'] === 'Ochiq Hudud');
        });
    }

    public function test_draft_historical_region_is_never_visible(): void
    {
        HistoricalRegion::factory()->create([
            'name' => 'Yashirin Hudud',
            'status' => 'draft',
            'geojson' => json_decode($this->polygonGeoJson(), true),
        ]);

        $response = $this->get('/xarita');

        $response->assertViewHas('historicalRegions', function ($geojson) {
            return collect($geojson['features'])->doesntContain(fn ($f) => $f['properties']['name'] === 'Yashirin Hudud');
        });
    }

    public function test_published_region_without_geojson_is_excluded(): void
    {
        HistoricalRegion::factory()->published()->create([
            'name' => 'Geometriyasiz Hudud',
            'geojson' => null,
        ]);

        $response = $this->get('/xarita');

        $response->assertViewHas('historicalRegions', function ($geojson) {
            return collect($geojson['features'])->doesntContain(fn ($f) => $f['properties']['name'] === 'Geometriyasiz Hudud');
        });
    }

    public function test_draft_historical_map_layer_is_never_visible(): void
    {
        HistoricalMapLayer::factory()->create([
            'title' => 'Yashirin Qatlam',
            'status' => 'draft',
            'geojson' => json_decode($this->polygonGeoJson(), true),
        ]);

        $response = $this->get('/xarita');

        $response->assertViewHas('historicalLayers', function ($geojson) {
            return collect($geojson['features'])->doesntContain(fn ($f) => $f['properties']['name'] === 'Yashirin Qatlam');
        });
    }

    public function test_published_historical_map_layer_is_visible(): void
    {
        HistoricalMapLayer::factory()->published()->create([
            'title' => 'Ochiq Qatlam',
            'geojson' => json_decode($this->polygonGeoJson(), true),
        ]);

        $response = $this->get('/xarita');

        $response->assertViewHas('historicalLayers', function ($geojson) {
            return collect($geojson['features'])->contains(fn ($f) => $f['properties']['name'] === 'Ochiq Qatlam');
        });
    }

    public function test_period_filter_restricts_historical_regions(): void
    {
        $periodA = Period::factory()->create();
        $periodB = Period::factory()->create();

        HistoricalRegion::factory()->published()->create([
            'name' => 'Davr A hududi',
            'period_id' => $periodA->id,
            'geojson' => json_decode($this->polygonGeoJson(), true),
        ]);
        HistoricalRegion::factory()->published()->create([
            'name' => 'Davr B hududi',
            'period_id' => $periodB->id,
            'geojson' => json_decode($this->polygonGeoJson(), true),
        ]);

        $response = $this->get('/xarita?period='.$periodA->id);

        $response->assertViewHas('historicalRegions', function ($geojson) {
            $names = collect($geojson['features'])->pluck('properties.name');

            return $names->contains('Davr A hududi') && ! $names->contains('Davr B hududi');
        });
    }

    public function test_accuracy_status_is_exposed_in_public_feature_properties(): void
    {
        HistoricalRegion::factory()->published()->verified()->create([
            'name' => 'Tasdiqlangan Hudud',
            'geojson' => json_decode($this->polygonGeoJson(), true),
        ]);

        $response = $this->get('/xarita');

        $response->assertViewHas('historicalRegions', function ($geojson) {
            $feature = collect($geojson['features'])->firstWhere('properties.name', 'Tasdiqlangan Hudud');

            return $feature && $feature['properties']['accuracyStatus'] === 'verified';
        });
    }

    public function test_unsafe_source_url_scheme_is_never_exposed_publicly(): void
    {
        $source = SourceReference::factory()->create(['url' => 'javascript:alert(1)']);
        $region = HistoricalRegion::factory()->published()->create([
            'name' => 'Xavfli Manba Hududi',
            'geojson' => json_decode($this->polygonGeoJson(), true),
        ]);
        $region->sourceReferences()->attach($source->id);

        $response = $this->get('/xarita');

        $response->assertOk();
        $response->assertDontSee('javascript:alert(1)', false);
    }

    public function test_map_response_never_leaks_script_tags_from_region_name(): void
    {
        HistoricalRegion::factory()->published()->create([
            'name' => '<script>alert(1)</script>',
            'geojson' => json_decode($this->polygonGeoJson(), true),
        ]);

        $response = $this->get('/xarita');

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_uprising_markers_still_render_alongside_historical_layers(): void
    {
        $uzgolon = Uzgolon::factory()->published()->create(['name' => 'Test Qozgoloni']);
        \App\Models\MapMarker::factory()->create([
            'uzgolon_id' => $uzgolon->id,
            'latitude' => 41.0,
            'longitude' => 64.0,
        ]);
        HistoricalRegion::factory()->published()->create([
            'name' => 'Yonma-yon Hudud',
            'geojson' => json_decode($this->polygonGeoJson(), true),
        ]);

        $response = $this->get('/xarita');

        $response->assertOk();
        $response->assertViewHas('geojson', function ($geojson) {
            return collect($geojson['features'])->contains(fn ($f) => $f['properties']['title'] === 'Test Qozgoloni');
        });
        $response->assertViewHas('historicalRegions', function ($geojson) {
            return collect($geojson['features'])->contains(fn ($f) => $f['properties']['name'] === 'Yonma-yon Hudud');
        });
    }

    public function test_guest_cannot_reach_admin_edit_for_any_layer_via_public_map(): void
    {
        $layer = HistoricalMapLayer::factory()->create();

        $this->get("/admin/historical-map-layers/{$layer->slug}/edit")->assertRedirect(route('login'));
    }

    public function test_map_query_count_does_not_grow_with_historical_data_size(): void
    {
        HistoricalRegion::factory()->count(5)->published()->create(['geojson' => json_decode($this->polygonGeoJson(), true)]);
        HistoricalMapLayer::factory()->count(5)->published()->create(['geojson' => json_decode($this->polygonGeoJson(), true)]);

        DB::enableQueryLog();
        $this->get('/xarita');
        $smallCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        DB::flushQueryLog();

        HistoricalRegion::factory()->count(20)->published()->create(['geojson' => json_decode($this->polygonGeoJson(), true)]);
        HistoricalMapLayer::factory()->count(20)->published()->create(['geojson' => json_decode($this->polygonGeoJson(), true)]);

        DB::enableQueryLog();
        $this->get('/xarita');
        $largeCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($smallCount, $largeCount);
    }
}
