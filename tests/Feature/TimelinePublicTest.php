<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Period;
use App\Models\Qorboshi;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Models\Uzgolon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TimelinePublicTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_event_is_visible_via_index_and_detail(): void
    {
        $event = TimelineEvent::factory()->published()->create(['title' => 'Ochiq voqea']);

        $this->get('/xronologiya')->assertOk()->assertSee('Ochiq voqea');
        $this->get(route('xronologiya.show', $event))->assertOk()->assertSee('Ochiq voqea');
    }

    public function test_draft_event_detail_returns_404(): void
    {
        $event = TimelineEvent::factory()->create(['title' => 'Qoralama voqea', 'status' => 'draft']);

        $this->get(route('xronologiya.show', $event))->assertNotFound();
    }

    public function test_draft_event_is_excluded_from_index(): void
    {
        TimelineEvent::factory()->create(['title' => 'Yashirin voqea', 'status' => 'draft']);

        $this->get('/xronologiya')->assertDontSee('Yashirin voqea');
    }

    public function test_draft_event_is_excluded_from_homepage_preview(): void
    {
        TimelineEvent::factory()->create(['title' => 'Bosh sahifada yoq voqea', 'status' => 'draft']);
        TimelineEvent::factory()->published()->create(['title' => 'Bosh sahifada bor voqea']);

        $response = $this->get('/');

        $response->assertSee('Bosh sahifada bor voqea');
        $response->assertDontSee('Bosh sahifada yoq voqea');
    }

    public function test_period_filter_narrows_index_results(): void
    {
        $periodA = Period::factory()->create();
        $periodB = Period::factory()->create();

        TimelineEvent::factory()->published()->create(['title' => 'Davr A voqeasi', 'period_id' => $periodA->id]);
        TimelineEvent::factory()->published()->create(['title' => 'Davr B voqeasi', 'period_id' => $periodB->id]);

        $response = $this->get('/xronologiya?period='.$periodA->id);

        $response->assertSee('Davr A voqeasi');
        $response->assertDontSee('Davr B voqeasi');
    }

    public function test_year_range_filter_only_returns_overlapping_events(): void
    {
        TimelineEvent::factory()->published()->create(['title' => 'Ichkarida', 'start_year' => 1918, 'end_year' => 1920]);
        TimelineEvent::factory()->published()->create(['title' => 'Tashqarida', 'start_year' => 1950, 'end_year' => 1955]);

        $response = $this->get('/xronologiya?from_year=1900&to_year=1930');

        $response->assertSee('Ichkarida');
        $response->assertDontSee('Tashqarida');
    }

    public function test_unpublished_related_qorboshi_is_not_leaked_on_published_event(): void
    {
        $draftQorboshi = Qorboshi::factory()->create(['full_name' => 'Draft Qorboshi', 'status' => 'draft']);
        $event = TimelineEvent::factory()->published()->create(['qorboshi_id' => $draftQorboshi->id]);

        $response = $this->get(route('xronologiya.show', $event));

        $response->assertOk();
        $response->assertDontSee('Draft Qorboshi');
    }

    public function test_unpublished_related_uzgolon_is_not_leaked_on_published_event(): void
    {
        $draftUzgolon = Uzgolon::factory()->create(['name' => 'Draft Uzgolon', 'status' => 'draft']);
        $event = TimelineEvent::factory()->published()->create(['uzgolon_id' => $draftUzgolon->id]);

        $response = $this->get(route('xronologiya.show', $event));

        $response->assertOk();
        $response->assertDontSee('Draft Uzgolon');
    }

    public function test_xss_in_title_and_description_never_renders_unescaped(): void
    {
        $event = TimelineEvent::factory()->published()->create([
            'title' => '<script>alert(1)</script>',
            'description' => '<script>alert(2)</script>',
        ]);

        $response = $this->get(route('xronologiya.show', $event));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertDontSee('<script>alert(2)</script>', false);
    }

    public function test_event_is_searchable_when_published(): void
    {
        TimelineEvent::factory()->published()->create(['title' => 'Qidiruv Sinovi Voqeasi']);

        $response = $this->get('/qidiruv?q=Qidiruv+Sinovi');

        $response->assertOk();
        $response->assertSee('Qidiruv Sinovi Voqeasi');
    }

    public function test_draft_event_is_not_searchable(): void
    {
        TimelineEvent::factory()->create(['title' => 'Yashirin Qidiruv Voqeasi', 'status' => 'draft']);

        $response = $this->get('/qidiruv?q=Yashirin+Qidiruv');

        $response->assertDontSee('Yashirin Qidiruv Voqeasi');
    }

    public function test_search_type_filter_accepts_timeline_event(): void
    {
        TimelineEvent::factory()->published()->create(['title' => 'TypeFilter Voqeasi']);

        $response = $this->get('/qidiruv?q=TypeFilter&type=timeline_event');

        $response->assertOk();
        $response->assertSee('TypeFilter Voqeasi');
    }

    public function test_guest_sees_login_prompt_instead_of_comment_form(): void
    {
        $event = TimelineEvent::factory()->published()->create();

        $response = $this->get(route('xronologiya.show', $event));

        $response->assertOk();
        $response->assertSee('tizimga kiring');
    }

    public function test_authenticated_user_can_comment_on_published_event(): void
    {
        $user = User::factory()->create();
        $event = TimelineEvent::factory()->published()->create();

        $response = $this->actingAs($user)->post('/comments', [
            'commentable_type' => 'timeline_event',
            'commentable_id' => $event->id,
            'content' => 'Juda qiziqarli voqea!',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('comments', [
            'commentable_type' => TimelineEvent::class,
            'commentable_id' => $event->id,
            'status' => 'pending',
        ]);
    }

    public function test_comment_on_draft_event_is_rejected(): void
    {
        $user = User::factory()->create();
        $event = TimelineEvent::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($user)->post('/comments', [
            'commentable_type' => 'timeline_event',
            'commentable_id' => $event->id,
            'content' => 'Draftga izoh',
        ]);

        $response->assertSessionHasErrors('commentable_id');
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_user_cannot_delete_another_users_comment_on_event(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $event = TimelineEvent::factory()->published()->create();
        $comment = Comment::factory()->for($owner, 'author')->for($event, 'commentable')->create();

        $response = $this->actingAs($intruder)->delete("/comments/{$comment->id}");

        $response->assertForbidden();
        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_map_shows_event_coordinates_when_present(): void
    {
        $event = TimelineEvent::factory()->published()->withCoordinates()->create(['title' => 'Xaritadagi voqea']);

        $response = $this->get('/xarita');

        $response->assertOk();
        $response->assertViewHas('timelineEvents', function ($geojson) {
            return collect($geojson['features'])->contains(fn ($f) => $f['properties']['title'] === 'Xaritadagi voqea');
        });
    }

    public function test_map_excludes_event_without_coordinates(): void
    {
        TimelineEvent::factory()->published()->create(['title' => 'Koordinatasiz voqea', 'latitude' => null, 'longitude' => null]);

        $response = $this->get('/xarita');

        $response->assertViewHas('timelineEvents', function ($geojson) {
            return collect($geojson['features'])->doesntContain(fn ($f) => $f['properties']['title'] === 'Koordinatasiz voqea');
        });
    }

    public function test_map_excludes_draft_event_coordinates(): void
    {
        TimelineEvent::factory()->withCoordinates()->create(['title' => 'Draft xarita voqeasi', 'status' => 'draft']);

        $response = $this->get('/xarita');

        $response->assertViewHas('timelineEvents', function ($geojson) {
            return collect($geojson['features'])->doesntContain(fn ($f) => $f['properties']['title'] === 'Draft xarita voqeasi');
        });
    }

    public function test_map_period_filter_restricts_timeline_events(): void
    {
        $periodA = Period::factory()->create();
        $periodB = Period::factory()->create();

        TimelineEvent::factory()->published()->withCoordinates()->create(['title' => 'A voqeasi', 'period_id' => $periodA->id]);
        TimelineEvent::factory()->published()->withCoordinates()->create(['title' => 'B voqeasi', 'period_id' => $periodB->id]);

        $response = $this->get('/xarita?period='.$periodA->id);

        $response->assertViewHas('timelineEvents', function ($geojson) {
            $titles = collect($geojson['features'])->pluck('properties.title');

            return $titles->contains('A voqeasi') && ! $titles->contains('B voqeasi');
        });
    }

    public function test_map_never_leaks_script_tags_from_event_title(): void
    {
        TimelineEvent::factory()->published()->withCoordinates()->create(['title' => '<script>alert(3)</script>']);

        $response = $this->get('/xarita');

        $response->assertOk();
        $response->assertDontSee('<script>alert(3)</script>', false);
    }

    public function test_guest_cannot_reach_admin_timeline_edit_via_public_map_flow(): void
    {
        $event = TimelineEvent::factory()->create();

        $this->get("/admin/timeline-events/{$event->slug}/edit")->assertRedirect(route('login'));
    }

    public function test_map_query_count_does_not_grow_with_timeline_event_count(): void
    {
        TimelineEvent::factory()->count(5)->published()->withCoordinates()->create();

        DB::enableQueryLog();
        $this->get('/xarita');
        $smallCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        DB::flushQueryLog();

        TimelineEvent::factory()->count(25)->published()->withCoordinates()->create();

        DB::enableQueryLog();
        $this->get('/xarita');
        $largeCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($smallCount, $largeCount);
    }

    public function test_public_index_query_count_does_not_grow_with_event_count(): void
    {
        $period = Period::factory()->create();
        TimelineEvent::factory()->count(5)->published()->create(['period_id' => $period->id]);

        DB::enableQueryLog();
        $this->get('/xronologiya');
        $smallCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        DB::flushQueryLog();

        TimelineEvent::factory()->count(25)->published()->create(['period_id' => $period->id]);

        DB::enableQueryLog();
        $this->get('/xronologiya');
        $largeCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($smallCount, $largeCount);
    }
}
