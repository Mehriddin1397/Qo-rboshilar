<?php

namespace Tests\Feature;

use App\Models\HistoricalRegion;
use App\Models\Period;
use App\Models\Region;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalRegionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_any_admin_historical_region_route(): void
    {
        $historicalRegion = HistoricalRegion::factory()->create();

        $this->get('/admin/historical-regions')->assertRedirect(route('login'));
        $this->get('/admin/historical-regions/create')->assertRedirect(route('login'));
        $this->get("/admin/historical-regions/{$historicalRegion->slug}/edit")->assertRedirect(route('login'));
        $this->get("/admin/historical-regions/{$historicalRegion->slug}")->assertRedirect(route('login'));
        $this->delete("/admin/historical-regions/{$historicalRegion->slug}")->assertRedirect(route('login'));
    }

    public function test_plain_user_cannot_access_admin_historical_region_routes(): void
    {
        $user = User::factory()->create();
        $historicalRegion = HistoricalRegion::factory()->create();

        $this->actingAs($user)->get('/admin/historical-regions')->assertForbidden();
        $this->actingAs($user)->get('/admin/historical-regions/create')->assertForbidden();
        $this->actingAs($user)->get("/admin/historical-regions/{$historicalRegion->slug}/edit")->assertForbidden();
    }

    public function test_editor_cannot_access_admin_historical_region_routes(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get('/admin/historical-regions')->assertForbidden();
    }

    public function test_admin_can_perform_full_crud(): void
    {
        $admin = User::factory()->admin()->create();
        $period = Period::factory()->create();
        $region = Region::factory()->create();

        $this->actingAs($admin)->get('/admin/historical-regions')->assertOk();
        $this->actingAs($admin)->get('/admin/historical-regions/create')->assertOk();

        $createResponse = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Test tarixiy hudud',
            'period_id' => $period->id,
            'region_id' => $region->id,
            'region_type' => 'other',
            'description' => '[DEMO DATA] test tavsif',
            'geojson' => json_encode(['type' => 'FeatureCollection', 'features' => []]),
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'featured' => '0',
            'sort_order' => 3,
        ]);

        $createResponse->assertRedirect(route('admin.historical-regions.index'));
        $this->assertDatabaseHas('historical_regions', ['name' => '[DEMO] Test tarixiy hudud', 'sort_order' => 3]);

        $historicalRegion = HistoricalRegion::where('name', '[DEMO] Test tarixiy hudud')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.historical-regions.show', $historicalRegion))->assertOk();
        $this->actingAs($admin)->get(route('admin.historical-regions.edit', $historicalRegion))->assertOk();

        $updateResponse = $this->actingAs($admin)->put(route('admin.historical-regions.update', $historicalRegion), [
            'name' => '[DEMO] Yangilangan hudud',
            'region_type' => 'other',
            'status' => 'published',
            'accuracy_status' => 'approximate',
            'featured' => '1',
            'sort_order' => 5,
        ]);

        $updateResponse->assertRedirect(route('admin.historical-regions.index'));
        $this->assertDatabaseHas('historical_regions', [
            'id' => $historicalRegion->id,
            'name' => '[DEMO] Yangilangan hudud',
            'status' => 'published',
            'featured' => true,
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.historical-regions.destroy', $historicalRegion->fresh()));
        $deleteResponse->assertRedirect(route('admin.historical-regions.index'));
        $this->assertDatabaseMissing('historical_regions', ['id' => $historicalRegion->id]);
    }

    public function test_slug_is_stable_on_update_unless_explicitly_changed(): void
    {
        $admin = User::factory()->admin()->create();
        $historicalRegion = HistoricalRegion::factory()->create(['name' => 'Original nomi', 'slug' => 'original-nomi']);

        $this->actingAs($admin)->put(route('admin.historical-regions.update', $historicalRegion), [
            'name' => 'Butunlay boshqa nom',
            'status' => 'draft',
        ]);

        $this->assertSame('original-nomi', $historicalRegion->fresh()->slug);
    }

    public function test_name_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_slug_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        HistoricalRegion::factory()->create(['slug' => 'taken-slug']);

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Boshqa nom',
            'slug' => 'taken-slug',
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_period_and_region_must_exist(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Test',
            'period_id' => 9999,
            'region_id' => 9999,
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors(['period_id', 'region_id']);
    }

    public function test_valid_geojson_is_accepted(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Valid geojson',
            'region_type' => 'other',
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'geojson' => json_encode(['type' => 'FeatureCollection', 'features' => []]),
        ]);

        $response->assertRedirect(route('admin.historical-regions.index'));
        $this->assertDatabaseHas('historical_regions', ['name' => '[DEMO] Valid geojson']);
    }

    public function test_invalid_json_string_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Invalid geojson',
            'status' => 'draft',
            'geojson' => 'not-json',
        ]);

        $response->assertSessionHasErrors('geojson');
        $this->assertDatabaseMissing('historical_regions', ['name' => '[DEMO] Invalid geojson']);
    }

    public function test_valid_json_with_wrong_structure_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Wrong structure',
            'status' => 'draft',
            'geojson' => json_encode(['hello' => 'world']),
        ]);

        $response->assertSessionHasErrors('geojson');
    }

    public function test_invalid_status_and_sort_order_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Bad values',
            'status' => 'not-a-real-status',
            'sort_order' => -5,
        ]);

        $response->assertSessionHasErrors(['status', 'sort_order']);
    }

    public function test_deleting_region_nulls_out_dependent_layers_instead_of_deleting_them(): void
    {
        $admin = User::factory()->admin()->create();
        $historicalRegion = HistoricalRegion::factory()->create();
        $layer = \App\Models\HistoricalMapLayer::factory()->create(['historical_region_id' => $historicalRegion->id]);

        $this->actingAs($admin)->delete(route('admin.historical-regions.destroy', $historicalRegion));

        $this->assertDatabaseHas('historical_map_layers', ['id' => $layer->id, 'historical_region_id' => null]);
    }

    public function test_geojson_property_containing_script_tag_is_never_rendered_as_raw_html(): void
    {
        $admin = User::factory()->admin()->create();
        $historicalRegion = HistoricalRegion::factory()->create([
            'geojson' => [
                'type' => 'FeatureCollection',
                'features' => [[
                    'type' => 'Feature',
                    'geometry' => ['type' => 'Point', 'coordinates' => [64.5, 41.2]],
                    'properties' => ['name' => '<script>alert(1)</script>'],
                ]],
            ],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.historical-regions.show', $historicalRegion));

        $response->assertOk();
        // @js() JSON-encodes for a <script> data island — the literal HTML tag string
        // must never appear unescaped/executable in the response body.
        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_index_eager_loads_period_and_region_without_n_plus_one(): void
    {
        $admin = User::factory()->admin()->create();
        $period = Period::factory()->create();
        $region = Region::factory()->create();

        HistoricalRegion::factory()->count(3)->create(['period_id' => $period->id, 'region_id' => $region->id]);

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/historical-regions');
        $smallCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
        \Illuminate\Support\Facades\DB::disableQueryLog();
        \Illuminate\Support\Facades\DB::flushQueryLog();

        HistoricalRegion::factory()->count(12)->create(['period_id' => $period->id, 'region_id' => $region->id]);

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/historical-regions');
        $largeCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $this->assertSame($smallCount, $largeCount);
    }

    public function test_coordinates_out_of_range_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Out of range',
            'region_type' => 'other',
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'geojson' => json_encode([
                'type' => 'Polygon',
                'coordinates' => [[[999, 41], [65, 999], [65, 41], [64, 41]]],
            ]),
        ]);

        $response->assertSessionHasErrors('geojson');
    }

    public function test_valid_multipolygon_with_in_range_coordinates_is_accepted(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Valid multipolygon',
            'region_type' => 'geographical',
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'geojson' => json_encode([
                'type' => 'MultiPolygon',
                'coordinates' => [[[[64, 41], [65, 41], [65, 42], [64, 41]]]],
            ]),
        ]);

        $response->assertRedirect(route('admin.historical-regions.index'));
        $this->assertDatabaseHas('historical_regions', ['name' => '[DEMO] Valid multipolygon']);
    }

    public function test_unsupported_geometry_type_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Unsupported type',
            'region_type' => 'other',
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'geojson' => json_encode(['type' => 'GeometryCollection', 'geometries' => []]),
        ]);

        $response->assertSessionHasErrors('geojson');
    }

    public function test_publishing_real_geometry_without_source_is_blocked(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] No source',
            'region_type' => 'other',
            'status' => 'published',
            'accuracy_status' => 'uncertain',
            'geojson' => json_encode([
                'type' => 'Polygon',
                'coordinates' => [[[64, 41], [65, 41], [65, 42], [64, 41]]],
            ]),
        ]);

        $response->assertSessionHasErrors('source_reference_ids');
        $this->assertDatabaseMissing('historical_regions', ['name' => '[DEMO] No source']);
    }

    public function test_publishing_real_geometry_with_source_succeeds(): void
    {
        $admin = User::factory()->admin()->create();
        $source = \App\Models\SourceReference::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] With source',
            'region_type' => 'political',
            'status' => 'published',
            'accuracy_status' => 'approximate',
            'geojson' => json_encode([
                'type' => 'Polygon',
                'coordinates' => [[[64, 41], [65, 41], [65, 42], [64, 41]]],
            ]),
            'source_reference_ids' => [$source->id],
        ]);

        $response->assertRedirect(route('admin.historical-regions.index'));
        $region = HistoricalRegion::where('name', '[DEMO] With source')->firstOrFail();
        $this->assertTrue($region->sourceReferences->contains('id', $source->id));
    }

    public function test_publishing_empty_placeholder_geometry_does_not_require_source(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Empty placeholder',
            'region_type' => 'other',
            'status' => 'published',
            'accuracy_status' => 'uncertain',
            'geojson' => json_encode(['type' => 'FeatureCollection', 'features' => []]),
        ]);

        $response->assertRedirect(route('admin.historical-regions.index'));
    }

    public function test_accuracy_status_is_independent_of_publish_status(): void
    {
        $admin = User::factory()->admin()->create();
        $source = \App\Models\SourceReference::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Published but approximate',
            'region_type' => 'other',
            'status' => 'published',
            'accuracy_status' => 'approximate',
            'geojson' => json_encode([
                'type' => 'Polygon',
                'coordinates' => [[[64, 41], [65, 41], [65, 42], [64, 41]]],
            ]),
            'source_reference_ids' => [$source->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('historical_regions', [
            'name' => '[DEMO] Published but approximate',
            'status' => 'published',
            'accuracy_status' => 'approximate',
        ]);
    }

    public function test_invalid_region_type_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Bad type',
            'region_type' => 'invented-type',
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
        ]);

        $response->assertSessionHasErrors('region_type');
    }

    public function test_invalid_accuracy_status_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-regions', [
            'name' => '[DEMO] Bad accuracy',
            'region_type' => 'other',
            'status' => 'draft',
            'accuracy_status' => 'super-duper-sure',
        ]);

        $response->assertSessionHasErrors('accuracy_status');
    }
}
