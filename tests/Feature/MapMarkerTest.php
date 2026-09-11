<?php

namespace Tests\Feature;

use App\Models\MapMarker;
use App\Models\User;
use App\Models\Uzgolon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MapMarkerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_any_admin_map_marker_route(): void
    {
        $marker = MapMarker::factory()->create();

        $this->get('/admin/map-markers')->assertRedirect(route('login'));
        $this->get('/admin/map-markers/create')->assertRedirect(route('login'));
        $this->get("/admin/map-markers/{$marker->id}")->assertRedirect(route('login'));
        $this->get("/admin/map-markers/{$marker->id}/edit")->assertRedirect(route('login'));
        $this->delete("/admin/map-markers/{$marker->id}")->assertRedirect(route('login'));
    }

    public function test_plain_user_cannot_access_admin_map_marker_routes(): void
    {
        $user = User::factory()->create();
        $marker = MapMarker::factory()->create();

        $this->actingAs($user)->get('/admin/map-markers')->assertForbidden();
        $this->actingAs($user)->get('/admin/map-markers/create')->assertForbidden();
        $this->actingAs($user)->get("/admin/map-markers/{$marker->id}/edit")->assertForbidden();
    }

    public function test_editor_cannot_access_admin_map_marker_routes(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get('/admin/map-markers')->assertForbidden();
    }

    public function test_admin_can_perform_full_crud(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/map-markers')->assertOk();
        $this->actingAs($admin)->get('/admin/map-markers/create')->assertOk();

        $createResponse = $this->actingAs($admin)->post('/admin/map-markers', [
            'title' => '[DEMO DATA] Test marker',
            'latitude' => 41.311081,
            'longitude' => 69.240562,
            'description' => '[DEMO DATA] test tavsif',
            'type' => 'other',
            'status' => 'draft',
            'sort_order' => 1,
        ]);

        $createResponse->assertRedirect(route('admin.map-markers.index'));
        $this->assertDatabaseHas('map_markers', ['title' => '[DEMO DATA] Test marker', 'status' => 'draft']);

        $marker = MapMarker::where('title', '[DEMO DATA] Test marker')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.map-markers.show', $marker))->assertOk();
        $this->actingAs($admin)->get(route('admin.map-markers.edit', $marker))->assertOk();

        $updateResponse = $this->actingAs($admin)->put(route('admin.map-markers.update', $marker), [
            'title' => '[DEMO DATA] Yangilangan marker',
            'latitude' => 41.0,
            'longitude' => 69.0,
            'type' => 'battle',
            'status' => 'published',
            'sort_order' => 2,
        ]);

        $updateResponse->assertRedirect(route('admin.map-markers.index'));
        $this->assertDatabaseHas('map_markers', [
            'id' => $marker->id,
            'title' => '[DEMO DATA] Yangilangan marker',
            'status' => 'published',
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.map-markers.destroy', $marker->fresh()));
        $deleteResponse->assertRedirect(route('admin.map-markers.index'));
        $this->assertDatabaseMissing('map_markers', ['id' => $marker->id]);
    }

    public function test_title_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/map-markers', [
            'latitude' => 41.0,
            'longitude' => 69.0,
            'type' => 'other',
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_coordinates_are_required(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/map-markers', [
            'title' => '[DEMO DATA] No coords',
            'type' => 'other',
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_coordinates_out_of_range_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/map-markers', [
            'title' => '[DEMO DATA] Out of range',
            'latitude' => 999,
            'longitude' => -999,
            'type' => 'other',
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_uzgolon_must_exist(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/map-markers', [
            'title' => '[DEMO DATA] Bad uzgolon',
            'latitude' => 41.0,
            'longitude' => 69.0,
            'type' => 'other',
            'status' => 'draft',
            'uzgolon_id' => 9999,
        ]);

        $response->assertSessionHasErrors('uzgolon_id');
    }

    public function test_setting_marker_as_primary_demotes_previous_primary_marker(): void
    {
        $admin = User::factory()->admin()->create();
        $uzgolon = Uzgolon::factory()->create();
        $existingPrimary = MapMarker::factory()->create(['uzgolon_id' => $uzgolon->id, 'is_primary' => true]);

        $response = $this->actingAs($admin)->post('/admin/map-markers', [
            'title' => '[DEMO DATA] New primary',
            'uzgolon_id' => $uzgolon->id,
            'latitude' => 41.0,
            'longitude' => 69.0,
            'type' => 'battle',
            'status' => 'published',
            'is_primary' => '1',
        ]);

        $response->assertRedirect(route('admin.map-markers.index'));
        $this->assertFalse($existingPrimary->fresh()->is_primary);
        $this->assertTrue(MapMarker::where('title', '[DEMO DATA] New primary')->firstOrFail()->is_primary);
    }

    public function test_primary_flag_is_ignored_when_no_uzgolon_is_linked(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/admin/map-markers', [
            'title' => '[DEMO DATA] Standalone primary attempt',
            'latitude' => 41.0,
            'longitude' => 69.0,
            'type' => 'other',
            'status' => 'published',
            'is_primary' => '1',
        ]);

        $marker = MapMarker::where('title', '[DEMO DATA] Standalone primary attempt')->firstOrFail();
        $this->assertFalse($marker->is_primary);
    }

    public function test_uzgolon_select_options_do_not_cause_n_plus_one_on_create_page(): void
    {
        $admin = User::factory()->admin()->create();

        Uzgolon::factory()->count(3)->create();

        DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/map-markers/create');
        $smallCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        DB::flushQueryLog();

        Uzgolon::factory()->count(12)->create();

        DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/map-markers/create');
        $largeCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($smallCount, $largeCount);
    }

    public function test_published_standalone_marker_with_valid_coordinates_is_visible_on_public_map(): void
    {
        MapMarker::factory()->secondary()->create([
            'title' => 'Ochiq mustaqil marker',
            'latitude' => 41.2,
            'longitude' => 64.5,
            'status' => 'published',
            'uzgolon_id' => null,
        ]);

        $response = $this->get('/xarita');

        $response->assertOk();
        $response->assertViewHas('geojson', function ($geojson) {
            return collect($geojson['features'])->contains(fn ($f) => $f['properties']['title'] === 'Ochiq mustaqil marker');
        });
    }

    public function test_draft_marker_is_never_visible_on_public_map(): void
    {
        MapMarker::factory()->secondary()->draft()->create([
            'title' => 'Yashirin marker',
            'latitude' => 41.2,
            'longitude' => 64.5,
            'uzgolon_id' => null,
        ]);

        $response = $this->get('/xarita');

        $response->assertViewHas('geojson', function ($geojson) {
            return collect($geojson['features'])->doesntContain(fn ($f) => $f['properties']['title'] === 'Yashirin marker');
        });
    }

    public function test_secondary_marker_of_draft_uzgolon_is_never_visible_on_public_map(): void
    {
        $uzgolon = Uzgolon::factory()->create(['status' => 'draft']);
        MapMarker::factory()->secondary()->create([
            'title' => 'Qoralama qozgolon markeri',
            'latitude' => 41.2,
            'longitude' => 64.5,
            'status' => 'published',
            'uzgolon_id' => $uzgolon->id,
        ]);

        $response = $this->get('/xarita');

        $response->assertViewHas('geojson', function ($geojson) {
            return collect($geojson['features'])->doesntContain(fn ($f) => $f['properties']['title'] === 'Qoralama qozgolon markeri');
        });
    }

    public function test_primary_marker_is_not_duplicated_by_standalone_public_geojson(): void
    {
        $uzgolon = Uzgolon::factory()->published()->create(['name' => 'Yagona Qozgolon']);
        MapMarker::factory()->create([
            'uzgolon_id' => $uzgolon->id,
            'is_primary' => true,
            'status' => 'published',
            'latitude' => 41.2,
            'longitude' => 64.5,
        ]);

        $response = $this->get('/xarita');

        $response->assertViewHas('geojson', function ($geojson) {
            return collect($geojson['features'])->where('properties.title', 'Yagona Qozgolon')->count() === 1;
        });
    }
}
