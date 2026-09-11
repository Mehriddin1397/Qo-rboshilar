<?php

namespace Tests\Feature;

use App\Enums\BlogStatus;
use App\Models\Blog;
use App\Models\Literature;
use App\Models\Qorboshi;
use App\Models\Uzgolon;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_load_search_page(): void
    {
        $this->get('/qidiruv')->assertOk();
    }

    public function test_empty_query_shows_empty_state_without_results(): void
    {
        $response = $this->get('/qidiruv?q=');

        $response->assertOk();
        $response->assertViewHas('results', null);
        $response->assertSee("Qidiruv so'zini kiriting");
    }

    public function test_published_qorboshi_appears_in_results(): void
    {
        Qorboshi::factory()->published()->create(['full_name' => 'Madaminbek Botir']);

        $response = $this->get('/qidiruv?q=madaminbek');

        $response->assertOk();
        $response->assertSee('Madaminbek Botir');
    }

    public function test_draft_content_is_excluded_from_results(): void
    {
        Qorboshi::factory()->create(['full_name' => 'Draftbek Yashirin', 'status' => 'draft']);

        $response = $this->get('/qidiruv?q=draftbek');

        $response->assertOk();
        $response->assertDontSee('Draftbek Yashirin');
    }

    public function test_pending_blog_is_excluded_from_results(): void
    {
        Blog::factory()->pending()->create(['title' => 'Kutilayotgan Maqola']);

        $response = $this->get('/qidiruv?q=kutilayotgan');

        $response->assertDontSee('Kutilayotgan Maqola');
    }

    public function test_rejected_blog_is_excluded_from_results(): void
    {
        Blog::factory()->create(['title' => 'Rad Etilgan Maqola', 'status' => BlogStatus::Rejected]);

        $response = $this->get('/qidiruv?q=etilgan');

        $response->assertDontSee('Rad Etilgan Maqola');
    }

    public function test_type_filter_restricts_results_to_one_content_type(): void
    {
        Qorboshi::factory()->published()->create(['full_name' => 'Filtr Sinovi']);
        Uzgolon::factory()->published()->create(['name' => 'Filtr Sinovi qozgoloni']);

        $response = $this->get('/qidiruv?q=filtr&type=qorboshi');

        $response->assertSee('Filtr Sinovi');
        $response->assertDontSee('Filtr Sinovi qozgoloni');
    }

    public function test_each_type_filter_value_is_accepted(): void
    {
        Qorboshi::factory()->published()->create(['full_name' => 'TypeTest Qorboshi']);
        Uzgolon::factory()->published()->create(['name' => 'TypeTest Uzgolon']);
        Literature::factory()->published()->create(['title' => 'TypeTest Literature']);
        Video::factory()->published()->create(['title' => 'TypeTest Video']);
        Blog::factory()->approved()->create(['title' => 'TypeTest Blog']);

        foreach (['qorboshi', 'uzgolon', 'literature', 'video', 'blog'] as $type) {
            $this->get("/qidiruv?q=typetest&type={$type}")->assertOk();
        }
    }

    public function test_invalid_type_is_rejected(): void
    {
        $response = $this->get('/qidiruv?q=test&type=hacker-model');

        $response->assertSessionHasErrors('type');
    }

    public function test_search_query_is_escaped_in_output(): void
    {
        $response = $this->get('/qidiruv?'.http_build_query(['q' => '<script>alert(1)</script>']));

        $response->assertOk();
        $response->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_query_length_is_capped(): void
    {
        $response = $this->get('/qidiruv?q='.str_repeat('a', 200));

        $response->assertSessionHasErrors('q');
    }

    public function test_pagination_preserves_query_and_type(): void
    {
        Qorboshi::factory()->count(20)->published()->create(['full_name' => 'Sahifalash Sinovi']);

        $response = $this->get('/qidiruv?q=sahifalash&type=qorboshi&page=2');

        $response->assertOk();
        $response->assertViewHas('results', function ($results) {
            return $results->currentPage() === 2;
        });
    }

    public function test_search_query_count_does_not_grow_with_result_size(): void
    {
        Qorboshi::factory()->count(5)->published()->create(['full_name' => 'Kichik guruh sinovi']);

        DB::enableQueryLog();
        $this->get('/qidiruv?q=kichik+guruh');
        $smallCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        DB::flushQueryLog();

        Qorboshi::factory()->count(30)->published()->create(['full_name' => 'Kichik guruh sinovi']);

        DB::enableQueryLog();
        $this->get('/qidiruv?q=kichik+guruh');
        $largeCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($smallCount, $largeCount);
    }
}
