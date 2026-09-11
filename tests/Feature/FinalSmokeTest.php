<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Production QA §34: to'liq rol-asosli smoke test — Guest/User/Editor/Admin
 * har biri o'z ruxsatlari doirasida barcha asosiy sahifalarga kira olishini
 * tasdiqlaydi.
 */
class FinalSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_reach_all_public_pages(): void
    {
        // Diqqat: /robots.txt bu ro'yxatda yo'q — u public/robots.txt sifatida
        // veb-server tomonidan statik xizmat qilinadi (Laravel router'iga
        // umuman tegmaydi), shuning uchun PHPUnit test client (faqat Laravel
        // routing orqali ishlaydi) uni ko'rmaydi — bu productionda ishlashini
        // to'g'ridan-to'g'ri `curl` bilan tekshirish kerak (DEPLOYMENT.md §10).
        foreach ([
            '/', '/qorboshilar', '/qozgolonlar', '/xarita', '/xronologiya',
            '/adabiyotlar', '/videolar', '/bloglar', '/qidiruv', '/kirish',
            '/royxatdan-otish', '/boglanish', '/biz-haqimizda', '/sitemap.xml', '/up',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_user_can_login_view_profile_and_comment(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/profil')->assertOk();

        $qorboshi = \App\Models\Qorboshi::factory()->published()->create();
        $response = $this->actingAs($user)->post('/comments', [
            'commentable_type' => 'qorboshi',
            'commentable_id' => $qorboshi->id,
            'content' => 'Smoke test izohi',
        ]);
        $response->assertRedirect();

        $this->actingAs($user)->get('/bloglarim')->assertOk();
    }

    public function test_editor_can_moderate_but_not_access_admin_only_pages(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get('/admin/comments')->assertOk();
        $this->actingAs($editor)->get('/admin/bloglar')->assertOk();

        $this->actingAs($editor)->get('/admin/qorboshilar')->assertForbidden();
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
        $this->actingAs($editor)->get('/admin/timeline-events')->assertForbidden();
    }

    public function test_admin_has_full_access(): void
    {
        $admin = User::factory()->admin()->create();

        foreach ([
            '/admin', '/admin/qorboshilar', '/admin/qozgolonlar', '/admin/adabiyotlar',
            '/admin/videolar', '/admin/bloglar', '/admin/comments', '/admin/users',
            '/admin/historical-regions', '/admin/historical-map-layers', '/admin/timeline-events',
            '/admin/regions', '/admin/map-markers', '/admin/settings',
        ] as $path) {
            $this->actingAs($admin)->get($path)->assertOk();
        }
    }
}
