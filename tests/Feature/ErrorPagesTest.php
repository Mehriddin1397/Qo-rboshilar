<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;
    public function test_404_page_renders_custom_view(): void
    {
        $response = $this->get('/this-route-does-not-exist-at-all');
        $response->assertStatus(404);
        $response->assertSee('Sahifa topilmadi');
        $response->assertSee('Bosh sahifaga qaytish');
    }
    public function test_403_page_renders_custom_view(): void
    {
        $user = \App\Models\User::factory()->create();
        $response = $this->actingAs($user)->get('/admin/qorboshilar');
        $response->assertStatus(403);
        $response->assertSee('Ruxsat berilmagan');
    }
}
