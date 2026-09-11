<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Navbar simplification + role-based panel fix: navbar endi rolga qarab
 * to'g'ri havolani ko'rsatishini va /admin ruxsat matritsasini tasdiqlaydi.
 */
class NavbarRoleBasedTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_sees_login_and_register_links(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Kirish');
        $response->assertSee("Ro'yxatdan o'tish", false);
        $response->assertDontSee('Admin paneli');
        $response->assertDontSee('Tahrir paneli');
    }

    public function test_user_sees_profile_link_but_not_admin_or_editor_links(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertOk();
        $response->assertSee('Profil');
        $response->assertSee('Chiqish');
        $response->assertDontSee('Admin paneli');
        $response->assertDontSee('Tahrir paneli');
    }

    public function test_editor_sees_profile_and_editor_panel_but_not_admin_panel(): void
    {
        $editor = User::factory()->editor()->create();

        $response = $this->actingAs($editor)->get('/');

        $response->assertOk();
        $response->assertSee('Profil');
        $response->assertSee('Tahrir paneli');
        $response->assertSee('Chiqish');
        $response->assertDontSee('Admin paneli');
    }

    public function test_admin_sees_admin_panel_link_not_generic_user_panel(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/');

        $response->assertOk();
        $response->assertSee('Admin paneli');
        $response->assertSee('Chiqish');
        $response->assertDontSee('User panel');
        $response->assertDontSee('Tahrir paneli');
    }

    public function test_admin_root_authorization_matrix(): void
    {
        $user = User::factory()->create();
        $editor = User::factory()->editor()->create();
        $admin = User::factory()->admin()->create();

        $this->get('/admin')->assertRedirect(route('login'));
        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($editor)->get('/admin')->assertForbidden();
        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_about_page_is_reachable_from_navbar(): void
    {
        $this->get('/')->assertSee('href="/biz-haqimizda"', false);
        $this->get('/biz-haqimizda')->assertOk();
    }
}
