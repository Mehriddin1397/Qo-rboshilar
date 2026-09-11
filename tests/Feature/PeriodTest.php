<?php

namespace Tests\Feature;

use App\Models\HistoricalRegion;
use App\Models\Period;
use App\Models\User;
use App\Models\Uzgolon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_any_admin_period_route(): void
    {
        $period = Period::factory()->create();

        $this->get('/admin/periods')->assertRedirect(route('login'));
        $this->get('/admin/periods/create')->assertRedirect(route('login'));
        $this->get("/admin/periods/{$period->slug}")->assertRedirect(route('login'));
        $this->get("/admin/periods/{$period->slug}/edit")->assertRedirect(route('login'));
        $this->delete("/admin/periods/{$period->slug}")->assertRedirect(route('login'));
    }

    public function test_plain_user_cannot_access_admin_period_routes(): void
    {
        $user = User::factory()->create();
        $period = Period::factory()->create();

        $this->actingAs($user)->get('/admin/periods')->assertForbidden();
        $this->actingAs($user)->get('/admin/periods/create')->assertForbidden();
        $this->actingAs($user)->get("/admin/periods/{$period->slug}/edit")->assertForbidden();
    }

    public function test_editor_cannot_access_admin_period_routes(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get('/admin/periods')->assertForbidden();
    }

    public function test_admin_can_perform_full_crud(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/periods')->assertOk();
        $this->actingAs($admin)->get('/admin/periods/create')->assertOk();

        $createResponse = $this->actingAs($admin)->post('/admin/periods', [
            'name' => '[DEMO] Test davri',
            'start_year' => 1900,
            'end_year' => 1910,
            'description' => '[DEMO DATA] test tavsif',
        ]);

        $createResponse->assertRedirect(route('admin.periods.index'));
        $this->assertDatabaseHas('periods', ['name' => '[DEMO] Test davri', 'start_year' => 1900]);

        $period = Period::where('name', '[DEMO] Test davri')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.periods.show', $period))->assertOk();
        $this->actingAs($admin)->get(route('admin.periods.edit', $period))->assertOk();

        $updateResponse = $this->actingAs($admin)->put(route('admin.periods.update', $period), [
            'name' => '[DEMO] Yangilangan davr',
            'start_year' => 1905,
            'end_year' => 1915,
        ]);

        $updateResponse->assertRedirect(route('admin.periods.index'));
        $this->assertDatabaseHas('periods', [
            'id' => $period->id,
            'name' => '[DEMO] Yangilangan davr',
            'start_year' => 1905,
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.periods.destroy', $period->fresh()));
        $deleteResponse->assertRedirect(route('admin.periods.index'));
        $this->assertDatabaseMissing('periods', ['id' => $period->id]);
    }

    public function test_slug_is_stable_on_update_unless_explicitly_changed(): void
    {
        $admin = User::factory()->admin()->create();
        $period = Period::factory()->create(['name' => 'Original nomi', 'slug' => 'original-nomi']);

        $this->actingAs($admin)->put(route('admin.periods.update', $period), [
            'name' => 'Butunlay boshqa nom',
        ]);

        $this->assertSame('original-nomi', $period->fresh()->slug);
    }

    public function test_name_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/periods', []);

        $response->assertSessionHasErrors('name');
    }

    public function test_name_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Period::factory()->create(['name' => 'Band nom']);

        $response = $this->actingAs($admin)->post('/admin/periods', ['name' => 'Band nom']);

        $response->assertSessionHasErrors('name');
    }

    public function test_slug_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        Period::factory()->create(['slug' => 'taken-slug']);

        $response = $this->actingAs($admin)->post('/admin/periods', [
            'name' => '[DEMO] Boshqa nom',
            'slug' => 'taken-slug',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_end_year_must_be_gte_start_year(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/periods', [
            'name' => '[DEMO] Bad years',
            'start_year' => 1920,
            'end_year' => 1900,
        ]);

        $response->assertSessionHasErrors('end_year');
    }

    public function test_year_out_of_range_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/periods', [
            'name' => '[DEMO] Bad range',
            'start_year' => 500,
        ]);

        $response->assertSessionHasErrors('start_year');
    }

    public function test_deleting_period_with_related_uzgolon_is_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $period = Period::factory()->create();
        Uzgolon::factory()->create(['period_id' => $period->id]);

        $response = $this->actingAs($admin)->delete(route('admin.periods.destroy', $period));

        $response->assertRedirect(route('admin.periods.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('periods', ['id' => $period->id]);
    }

    public function test_deleting_period_with_related_historical_region_is_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $period = Period::factory()->create();
        HistoricalRegion::factory()->create(['period_id' => $period->id]);

        $response = $this->actingAs($admin)->delete(route('admin.periods.destroy', $period));

        $response->assertRedirect(route('admin.periods.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('periods', ['id' => $period->id]);
    }

    public function test_deleting_period_without_relations_succeeds(): void
    {
        $admin = User::factory()->admin()->create();
        $period = Period::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.periods.destroy', $period));

        $response->assertRedirect(route('admin.periods.index'));
        $this->assertDatabaseMissing('periods', ['id' => $period->id]);
    }

    public function test_index_shows_relation_counts_without_n_plus_one(): void
    {
        $admin = User::factory()->admin()->create();

        Period::factory()->count(3)->create();

        DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/periods');
        $smallCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        DB::flushQueryLog();

        Period::factory()->count(12)->create();

        DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/periods');
        $largeCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($smallCount, $largeCount);
    }

    public function test_demo_1918_1924_period_from_seeder_is_untouched_by_this_change(): void
    {
        $this->seed(\Database\Seeders\DemoSeeder::class);

        $this->assertDatabaseHas('periods', [
            'name' => '[DEMO] 1918-1924 davri',
            'start_year' => 1918,
            'end_year' => 1924,
        ]);
    }
}
