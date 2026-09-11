<?php

namespace Tests\Feature;

use App\Models\HistoricalRegion;
use App\Models\Period;
use App\Models\Qorboshi;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Models\Uzgolon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_any_admin_timeline_route(): void
    {
        $event = TimelineEvent::factory()->create();

        $this->get('/admin/timeline-events')->assertRedirect(route('login'));
        $this->get('/admin/timeline-events/create')->assertRedirect(route('login'));
        $this->get("/admin/timeline-events/{$event->slug}/edit")->assertRedirect(route('login'));
        $this->delete("/admin/timeline-events/{$event->slug}")->assertRedirect(route('login'));
    }

    public function test_plain_user_cannot_access_admin_timeline_routes(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/timeline-events')->assertForbidden();
    }

    public function test_editor_cannot_access_admin_timeline_routes(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get('/admin/timeline-events')->assertForbidden();
    }

    public function test_admin_can_perform_full_crud(): void
    {
        $admin = User::factory()->admin()->create();
        $period = Period::factory()->create();
        $qorboshi = Qorboshi::factory()->published()->create();

        $this->actingAs($admin)->get('/admin/timeline-events')->assertOk();
        $this->actingAs($admin)->get('/admin/timeline-events/create')->assertOk();

        $createResponse = $this->actingAs($admin)->post('/admin/timeline-events', [
            'title' => '[DEMO] Test voqeasi',
            'description' => '[DEMO DATA] test tavsif',
            'start_year' => 1918,
            'end_year' => 1920,
            'period_id' => $period->id,
            'qorboshi_id' => $qorboshi->id,
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'sort_order' => 1,
        ]);

        $createResponse->assertRedirect(route('admin.timeline-events.index'));
        $this->assertDatabaseHas('timeline_events', ['title' => '[DEMO] Test voqeasi', 'start_year' => 1918]);

        $event = TimelineEvent::where('title', '[DEMO] Test voqeasi')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.timeline-events.show', $event))->assertOk();
        $this->actingAs($admin)->get(route('admin.timeline-events.edit', $event))->assertOk();

        $updateResponse = $this->actingAs($admin)->put(route('admin.timeline-events.update', $event), [
            'title' => '[DEMO] Yangilangan voqea',
            'description' => '[DEMO DATA] yangilangan',
            'start_year' => 1919,
            'status' => 'published',
            'accuracy_status' => 'approximate',
        ]);

        $updateResponse->assertRedirect(route('admin.timeline-events.index'));
        $this->assertDatabaseHas('timeline_events', [
            'id' => $event->id,
            'title' => '[DEMO] Yangilangan voqea',
            'status' => 'published',
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.timeline-events.destroy', $event->fresh()));
        $deleteResponse->assertRedirect(route('admin.timeline-events.index'));
        $this->assertDatabaseMissing('timeline_events', ['id' => $event->id]);
    }

    public function test_slug_is_stable_on_update_unless_explicitly_changed(): void
    {
        $admin = User::factory()->admin()->create();
        $event = TimelineEvent::factory()->create(['title' => 'Original', 'slug' => 'original-slug']);

        $this->actingAs($admin)->put(route('admin.timeline-events.update', $event), [
            'title' => 'Butunlay boshqa nom',
            'description' => 'boshqa',
            'start_year' => 1900,
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
        ]);

        $this->assertSame('original-slug', $event->fresh()->slug);
    }

    public function test_title_is_required(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/timeline-events', [
            'description' => 'test',
            'start_year' => 1900,
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_slug_must_be_unique(): void
    {
        $admin = User::factory()->admin()->create();
        TimelineEvent::factory()->create(['slug' => 'taken-slug']);

        $response = $this->actingAs($admin)->post('/admin/timeline-events', [
            'title' => '[DEMO] Boshqa',
            'slug' => 'taken-slug',
            'description' => 'test',
            'start_year' => 1900,
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_start_year_is_required_and_bounded(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/timeline-events', [
            'title' => '[DEMO] No year',
            'description' => 'test',
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
        ]);
        $response->assertSessionHasErrors('start_year');

        $response = $this->actingAs($admin)->post('/admin/timeline-events', [
            'title' => '[DEMO] Bad year',
            'description' => 'test',
            'start_year' => 500,
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
        ]);
        $response->assertSessionHasErrors('start_year');
    }

    public function test_end_year_must_be_gte_start_year(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/timeline-events', [
            'title' => '[DEMO] Bad range',
            'description' => 'test',
            'start_year' => 1920,
            'end_year' => 1910,
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
        ]);

        $response->assertSessionHasErrors('end_year');
    }

    public function test_latitude_and_longitude_range_validation(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/timeline-events', [
            'title' => '[DEMO] Bad coords',
            'description' => 'test',
            'start_year' => 1900,
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'latitude' => 200,
            'longitude' => 500,
        ]);

        $response->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_latitude_requires_longitude_and_vice_versa(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/timeline-events', [
            'title' => '[DEMO] Only lat',
            'description' => 'test',
            'start_year' => 1900,
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'latitude' => 41.0,
        ]);

        $response->assertSessionHasErrors('longitude');
    }

    public function test_invalid_relation_ids_are_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/timeline-events', [
            'title' => '[DEMO] Bad relations',
            'description' => 'test',
            'start_year' => 1900,
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'period_id' => 9999,
            'qorboshi_id' => 9999,
            'uzgolon_id' => 9999,
            'historical_region_id' => 9999,
        ]);

        $response->assertSessionHasErrors(['period_id', 'qorboshi_id', 'uzgolon_id', 'historical_region_id']);
    }

    public function test_valid_coordinates_and_relations_are_accepted(): void
    {
        $admin = User::factory()->admin()->create();
        $period = Period::factory()->create();
        $qorboshi = Qorboshi::factory()->published()->create();
        $uzgolon = Uzgolon::factory()->published()->create();
        $historicalRegion = HistoricalRegion::factory()->published()->create();

        $response = $this->actingAs($admin)->post('/admin/timeline-events', [
            'title' => '[DEMO] Full relations',
            'description' => 'test',
            'start_year' => 1918,
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'period_id' => $period->id,
            'qorboshi_id' => $qorboshi->id,
            'uzgolon_id' => $uzgolon->id,
            'historical_region_id' => $historicalRegion->id,
            'latitude' => 41.2,
            'longitude' => 69.2,
        ]);

        $response->assertRedirect(route('admin.timeline-events.index'));
        $this->assertDatabaseHas('timeline_events', [
            'title' => '[DEMO] Full relations',
            'period_id' => $period->id,
            'qorboshi_id' => $qorboshi->id,
            'uzgolon_id' => $uzgolon->id,
            'historical_region_id' => $historicalRegion->id,
        ]);
    }

    public function test_source_references_are_synced(): void
    {
        $admin = User::factory()->admin()->create();
        $source = \App\Models\SourceReference::factory()->create();

        $response = $this->actingAs($admin)->post('/admin/timeline-events', [
            'title' => '[DEMO] Sourced event',
            'description' => 'test',
            'start_year' => 1918,
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'source_reference_ids' => [$source->id],
        ]);

        $response->assertRedirect();
        $event = TimelineEvent::where('title', '[DEMO] Sourced event')->firstOrFail();
        $this->assertTrue($event->sourceReferences->contains('id', $source->id));
    }

    public function test_mass_assignment_of_status_via_unexpected_field_is_ignored(): void
    {
        // FormRequest whitelists exact fields; extra/unexpected input keys are
        // simply not in $request->validated(), so they cannot reach the model.
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/timeline-events', [
            'title' => '[DEMO] Mass assignment test',
            'description' => 'test',
            'start_year' => 1900,
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'id' => 99999,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('timeline_events', ['id' => 99999]);
    }

    public function test_index_eager_loads_relations_without_n_plus_one(): void
    {
        $admin = User::factory()->admin()->create();
        $period = Period::factory()->create();

        TimelineEvent::factory()->count(5)->create(['period_id' => $period->id]);

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/timeline-events');
        $smallCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
        \Illuminate\Support\Facades\DB::disableQueryLog();
        \Illuminate\Support\Facades\DB::flushQueryLog();

        TimelineEvent::factory()->count(25)->create(['period_id' => $period->id]);

        \Illuminate\Support\Facades\DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/timeline-events');
        $largeCount = count(\Illuminate\Support\Facades\DB::getQueryLog());
        \Illuminate\Support\Facades\DB::disableQueryLog();

        $this->assertSame($smallCount, $largeCount);
    }
}
