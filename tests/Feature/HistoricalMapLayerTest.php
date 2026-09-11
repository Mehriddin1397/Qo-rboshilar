<?php

namespace Tests\Feature;

use App\Models\HistoricalMapLayer;
use App\Models\HistoricalRegion;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalMapLayerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_any_admin_map_layer_route(): void
    {
        $layer = HistoricalMapLayer::factory()->create();

        $this->get('/admin/historical-map-layers')->assertRedirect(route('login'));
        $this->get('/admin/historical-map-layers/create')->assertRedirect(route('login'));
        $this->get("/admin/historical-map-layers/{$layer->slug}")->assertRedirect(route('login'));
    }

    public function test_plain_user_cannot_access_admin_map_layer_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/historical-map-layers')->assertForbidden();
    }

    public function test_editor_cannot_access_admin_map_layer_routes(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get('/admin/historical-map-layers')->assertForbidden();
    }

    public function test_admin_can_perform_full_crud(): void
    {
        $admin = User::factory()->admin()->create();
        $period = Period::factory()->create();
        $historicalRegion = HistoricalRegion::factory()->create();

        $this->actingAs($admin)->get('/admin/historical-map-layers')->assertOk();
        $this->actingAs($admin)->get('/admin/historical-map-layers/create')->assertOk();

        $createResponse = $this->actingAs($admin)->post('/admin/historical-map-layers', [
            'title' => '[DEMO] Test qatlami',
            'period_id' => $period->id,
            'historical_region_id' => $historicalRegion->id,
            'description' => '[DEMO DATA] tavsif',
            'geojson' => json_encode(['type' => 'FeatureCollection', 'features' => []]),
            'opacity' => 0.5,
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'sort_order' => 2,
        ]);

        $createResponse->assertRedirect(route('admin.historical-map-layers.index'));
        $this->assertDatabaseHas('historical_map_layers', ['title' => '[DEMO] Test qatlami', 'sort_order' => 2]);

        $layer = HistoricalMapLayer::where('title', '[DEMO] Test qatlami')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.historical-map-layers.show', $layer))->assertOk();
        $this->actingAs($admin)->get(route('admin.historical-map-layers.edit', $layer))->assertOk();

        $updateResponse = $this->actingAs($admin)->put(route('admin.historical-map-layers.update', $layer), [
            'title' => '[DEMO] Yangilangan qatlam',
            'opacity' => 0.9,
            'status' => 'published',
            'accuracy_status' => 'approximate',
            'sort_order' => 7,
        ]);

        $updateResponse->assertRedirect(route('admin.historical-map-layers.index'));
        $this->assertDatabaseHas('historical_map_layers', [
            'id' => $layer->id,
            'title' => '[DEMO] Yangilangan qatlam',
            'status' => 'published',
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.historical-map-layers.destroy', $layer->fresh()));
        $deleteResponse->assertRedirect(route('admin.historical-map-layers.index'));
        $this->assertDatabaseMissing('historical_map_layers', ['id' => $layer->id]);
    }

    public function test_slug_is_stable_on_update_unless_explicitly_changed(): void
    {
        $admin = User::factory()->admin()->create();
        $layer = HistoricalMapLayer::factory()->create(['title' => 'Original', 'slug' => 'original-slug']);

        $this->actingAs($admin)->put(route('admin.historical-map-layers.update', $layer), [
            'title' => 'Butunlay boshqa nom',
            'status' => 'draft',
        ]);

        $this->assertSame('original-slug', $layer->fresh()->slug);
    }

    public function test_title_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-map-layers', ['status' => 'draft']);

        $response->assertSessionHasErrors('title');
    }

    public function test_period_and_historical_region_must_exist(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-map-layers', [
            'title' => '[DEMO] Test',
            'period_id' => 9999,
            'historical_region_id' => 9999,
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors(['period_id', 'historical_region_id']);
    }

    public function test_invalid_geojson_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-map-layers', [
            'title' => '[DEMO] Invalid',
            'status' => 'draft',
            'geojson' => 'not-json',
        ]);

        $response->assertSessionHasErrors('geojson');
    }

    public function test_valid_feature_collection_geojson_is_accepted(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-map-layers', [
            'title' => '[DEMO] Valid',
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'geojson' => json_encode(['type' => 'FeatureCollection', 'features' => []]),
        ]);

        $response->assertRedirect(route('admin.historical-map-layers.index'));
        $this->assertDatabaseHas('historical_map_layers', ['title' => '[DEMO] Valid']);
    }

    public function test_opacity_must_be_within_zero_and_one(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-map-layers', [
            'title' => '[DEMO] Bad opacity',
            'status' => 'draft',
            'opacity' => 1.5,
        ]);

        $response->assertSessionHasErrors('opacity');
    }

    public function test_invalid_status_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-map-layers', [
            'title' => '[DEMO] Bad status',
            'status' => 'not-a-real-status',
        ]);

        $response->assertSessionHasErrors('status');
    }

    public function test_layer_can_be_created_without_historical_region(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-map-layers', [
            'title' => '[DEMO] General overlay',
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
        ]);

        $response->assertRedirect(route('admin.historical-map-layers.index'));
        $this->assertDatabaseHas('historical_map_layers', ['title' => '[DEMO] General overlay', 'historical_region_id' => null]);
    }

    public function test_source_references_reuse_existing_polymorphic_architecture(): void
    {
        $admin = User::factory()->admin()->create();
        $source = \App\Models\SourceReference::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-map-layers', [
            'title' => '[DEMO] Sourced layer',
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'source_reference_ids' => [$source->id],
        ]);

        $response->assertRedirect();
        $layer = HistoricalMapLayer::where('title', '[DEMO] Sourced layer')->firstOrFail();

        $this->assertTrue($layer->sourceReferences->contains('id', $source->id));
        $this->assertDatabaseHas('sourceables', [
            'source_reference_id' => $source->id,
            'sourceable_type' => HistoricalMapLayer::class,
            'sourceable_id' => $layer->id,
        ]);
    }

    public function test_published_only_scope_excludes_draft_layers(): void
    {
        HistoricalMapLayer::factory()->published()->create(['title' => 'Published layer']);
        HistoricalMapLayer::factory()->create(['title' => 'Draft layer']);

        $published = HistoricalMapLayer::published()->pluck('title');

        $this->assertTrue($published->contains('Published layer'));
        $this->assertFalse($published->contains('Draft layer'));
    }

    public function test_index_eager_loads_period_and_historical_region_without_n_plus_one(): void
    {
        $admin = User::factory()->admin()->create();
        $period = Period::factory()->create();
        $historicalRegion = HistoricalRegion::factory()->create();

        HistoricalMapLayer::factory()->count(3)->create(['period_id' => $period->id, 'historical_region_id' => $historicalRegion->id]);

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/historical-map-layers');
        $smallCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
        \Illuminate\Support\Facades\DB::disableQueryLog();
        \Illuminate\Support\Facades\DB::flushQueryLog();

        HistoricalMapLayer::factory()->count(12)->create(['period_id' => $period->id, 'historical_region_id' => $historicalRegion->id]);

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/historical-map-layers');
        $largeCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $this->assertSame($smallCount, $largeCount);
    }

    public function test_publishing_real_geometry_without_source_is_blocked(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-map-layers', [
            'title' => '[DEMO] No source layer',
            'status' => 'published',
            'accuracy_status' => 'uncertain',
            'geojson' => json_encode([
                'type' => 'Polygon',
                'coordinates' => [[[64, 41], [65, 41], [65, 42], [64, 41]]],
            ]),
        ]);

        $response->assertSessionHasErrors('source_reference_ids');
        $this->assertDatabaseMissing('historical_map_layers', ['title' => '[DEMO] No source layer']);
    }

    public function test_publishing_real_geometry_with_source_succeeds(): void
    {
        $admin = User::factory()->admin()->create();
        $source = \App\Models\SourceReference::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-map-layers', [
            'title' => '[DEMO] With source layer',
            'status' => 'published',
            'accuracy_status' => 'verified',
            'geojson' => json_encode([
                'type' => 'Polygon',
                'coordinates' => [[[64, 41], [65, 41], [65, 42], [64, 41]]],
            ]),
            'source_reference_ids' => [$source->id],
        ]);

        $response->assertRedirect(route('admin.historical-map-layers.index'));
    }

    public function test_invalid_accuracy_status_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-map-layers', [
            'title' => '[DEMO] Bad accuracy',
            'status' => 'draft',
            'accuracy_status' => 'super-duper-sure',
        ]);

        $response->assertSessionHasErrors('accuracy_status');
    }

    public function test_coordinates_out_of_range_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/historical-map-layers', [
            'title' => '[DEMO] Bad coordinates',
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'geojson' => json_encode([
                'type' => 'Polygon',
                'coordinates' => [[[200, 41], [65, 41], [65, 42], [200, 41]]],
            ]),
        ]);

        $response->assertSessionHasErrors('geojson');
    }
}
