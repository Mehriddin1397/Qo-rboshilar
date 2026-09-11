<?php

namespace Tests\Feature;

use App\Models\Qorboshi;
use App\Models\Region;
use App\Models\User;
use App\Models\Uzgolon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_any_admin_region_route(): void
    {
        $region = Region::factory()->create();

        $this->get('/admin/regions')->assertRedirect(route('login'));
        $this->get('/admin/regions/create')->assertRedirect(route('login'));
        $this->get("/admin/regions/{$region->slug}")->assertRedirect(route('login'));
        $this->get("/admin/regions/{$region->slug}/edit")->assertRedirect(route('login'));
        $this->delete("/admin/regions/{$region->slug}")->assertRedirect(route('login'));
    }

    public function test_plain_user_cannot_access_admin_region_routes(): void
    {
        $user = User::factory()->create();
        $region = Region::factory()->create();

        $this->actingAs($user)->get('/admin/regions')->assertForbidden();
        $this->actingAs($user)->get('/admin/regions/create')->assertForbidden();
        $this->actingAs($user)->get("/admin/regions/{$region->slug}/edit")->assertForbidden();
    }

    public function test_editor_cannot_access_admin_region_routes(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get('/admin/regions')->assertForbidden();
    }

    public function test_admin_can_perform_full_crud(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/regions')->assertOk();
        $this->actingAs($admin)->get('/admin/regions/create')->assertOk();

        $createResponse = $this->actingAs($admin)->post('/admin/regions', [
            'name' => '[DEMO] Test hudud',
            'description' => '[DEMO DATA] test tavsif',
            'status' => 'draft',
            'sort_order' => 3,
        ]);

        $createResponse->assertRedirect(route('admin.regions.index'));
        $this->assertDatabaseHas('regions', ['name' => '[DEMO] Test hudud', 'sort_order' => 3, 'status' => 'draft']);

        $region = Region::where('name', '[DEMO] Test hudud')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.regions.show', $region))->assertOk();
        $this->actingAs($admin)->get(route('admin.regions.edit', $region))->assertOk();

        $updateResponse = $this->actingAs($admin)->put(route('admin.regions.update', $region), [
            'name' => '[DEMO] Yangilangan hudud',
            'status' => 'published',
            'sort_order' => 5,
        ]);

        $updateResponse->assertRedirect(route('admin.regions.index'));
        $this->assertDatabaseHas('regions', [
            'id' => $region->id,
            'name' => '[DEMO] Yangilangan hudud',
            'status' => 'published',
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.regions.destroy', $region->fresh()));
        $deleteResponse->assertRedirect(route('admin.regions.index'));
        $this->assertDatabaseMissing('regions', ['id' => $region->id]);
    }

    public function test_slug_is_stable_on_update_unless_explicitly_changed(): void
    {
        $admin = User::factory()->admin()->create();
        $region = Region::factory()->create(['name' => 'Original nomi', 'slug' => 'original-nomi']);

        $this->actingAs($admin)->put(route('admin.regions.update', $region), [
            'name' => 'Butunlay boshqa nom',
            'status' => 'draft',
        ]);

        $this->assertSame('original-nomi', $region->fresh()->slug);
    }

    public function test_name_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/regions', ['status' => 'draft']);

        $response->assertSessionHasErrors('name');
    }

    public function test_name_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Region::factory()->create(['name' => 'Band nom']);

        $response = $this->actingAs($admin)->post('/admin/regions', [
            'name' => 'Band nom',
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_slug_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Region::factory()->create(['slug' => 'taken-slug']);

        $response = $this->actingAs($admin)->post('/admin/regions', [
            'name' => '[DEMO] Boshqa nom',
            'slug' => 'taken-slug',
            'status' => 'draft',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_invalid_status_and_sort_order_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/regions', [
            'name' => '[DEMO] Bad values',
            'status' => 'not-a-real-status',
            'sort_order' => -5,
        ]);

        $response->assertSessionHasErrors(['status', 'sort_order']);
    }

    public function test_deleting_region_with_related_qorboshi_is_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $region = Region::factory()->create();
        Qorboshi::factory()->create(['region_id' => $region->id]);

        $response = $this->actingAs($admin)->delete(route('admin.regions.destroy', $region));

        $response->assertRedirect(route('admin.regions.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('regions', ['id' => $region->id]);
    }

    public function test_deleting_region_with_related_uzgolon_is_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $region = Region::factory()->create();
        Uzgolon::factory()->create(['region_id' => $region->id]);

        $response = $this->actingAs($admin)->delete(route('admin.regions.destroy', $region));

        $response->assertRedirect(route('admin.regions.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('regions', ['id' => $region->id]);
    }

    public function test_deleting_region_without_relations_succeeds(): void
    {
        $admin = User::factory()->admin()->create();
        $region = Region::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.regions.destroy', $region));

        $response->assertRedirect(route('admin.regions.index'));
        $this->assertDatabaseMissing('regions', ['id' => $region->id]);
    }

    public function test_index_shows_qorboshi_and_uzgolon_counts_without_n_plus_one(): void
    {
        $admin = User::factory()->admin()->create();

        Region::factory()->count(3)->create();

        DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/regions');
        $smallCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        DB::flushQueryLog();

        Region::factory()->count(12)->create();

        DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/regions');
        $largeCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($smallCount, $largeCount);
    }
}
