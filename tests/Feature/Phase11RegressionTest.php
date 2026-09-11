<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Literature;
use App\Models\Qorboshi;
use App\Models\User;
use App\Models\Uzgolon;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Faza 11 §57-58: mavjud Faza 8-10 sahifalari (comments komponenti qo'shilgandan
 * keyin ham) hammasi to'liq ishlashi kerak — bu smoke test har bir public detail
 * sahifani va admin panelning asosiy kirish nuqtalarini tekshiradi.
 */
class Phase11RegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_public_index_pages_load(): void
    {
        foreach (['/', '/qorboshilar', '/qozgolonlar', '/adabiyotlar', '/videolar', '/bloglar', '/xarita'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_all_public_detail_pages_load_with_comments_section(): void
    {
        $qorboshi = Qorboshi::factory()->published()->create();
        $uzgolon = Uzgolon::factory()->published()->create();
        $literature = Literature::factory()->published()->create();
        $video = Video::factory()->published()->create();
        $blog = Blog::factory()->approved()->create();

        $this->get(route('qorboshilar.show', $qorboshi))->assertOk()->assertSee('Izohlar');
        $this->get(route('qozgolonlar.show', $uzgolon))->assertOk()->assertSee('Izohlar');
        $this->get(route('adabiyotlar.show', $literature))->assertOk()->assertSee('Izohlar');
        $this->get(route('videolar.show', $video))->assertOk()->assertSee('Izohlar');
        $this->get(route('bloglar.show', $blog))->assertOk()->assertSee('Izohlar');
    }

    public function test_draft_detail_pages_still_404(): void
    {
        $qorboshi = Qorboshi::factory()->create(['status' => 'draft']);

        $this->get(route('qorboshilar.show', $qorboshi))->assertNotFound();
    }

    public function test_admin_dashboard_and_entity_pages_load_for_admin(): void
    {
        $admin = User::factory()->admin()->create();

        foreach ([
            '/admin', '/admin/qorboshilar', '/admin/qozgolonlar', '/admin/adabiyotlar',
            '/admin/videolar', '/admin/bloglar', '/admin/comments', '/admin/users',
        ] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }

    public function test_navbar_search_bar_points_to_search_route(): void
    {
        $this->get('/')->assertSee(route('search'), false);
    }
}
