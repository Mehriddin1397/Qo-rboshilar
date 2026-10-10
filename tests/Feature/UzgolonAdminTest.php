<?php

namespace Tests\Feature;

use App\Models\HistoricalImage;
use App\Models\Period;
use App\Models\Region;
use App\Models\User;
use App\Models\Uzgolon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UzgolonAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Uzgolon $uzgolon;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $region = Region::factory()->create();
        $period = Period::factory()->create(['start_year' => 1910, 'end_year' => 1920]);

        $this->uzgolon = Uzgolon::factory()->published()->create([
            'region_id' => $region->id,
            'period_id' => $period->id,
            'start_year' => 1916,
        ]);
    }

    public function test_admin_can_update_uzgolon_successfully(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.qozgolonlar.update', $this->uzgolon), [
            'name' => 'Yangilangan nom',
            'slug' => $this->uzgolon->slug,
            'short_description' => 'Yangi tavsif',
            'start_year' => 1916,
            'status' => 'published',
        ]);

        $response->assertRedirect(route('admin.qozgolonlar.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('uzgolonlar', [
            'id' => $this->uzgolon->id,
            'name' => 'Yangilangan nom',
        ]);
    }

    public function test_validation_fails_when_name_is_missing(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.qozgolonlar.update', $this->uzgolon), [
            'name' => '',
            'short_description' => 'Yangi tavsif',
            'start_year' => 1916,
            'status' => 'published',
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_validation_fails_when_cover_is_not_an_image(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->admin)->put(route('admin.qozgolonlar.update', $this->uzgolon), [
            'name' => 'Test nom',
            'short_description' => 'Yangi tavsif',
            'start_year' => 1916,
            'status' => 'published',
            'cover' => $file,
        ]);

        $response->assertSessionHasErrors([
            'cover' => 'Muqova fayli rasm bo\'lishi kerak.',
        ]);
    }

    public function test_validation_fails_when_text_exceeds_limits(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.qozgolonlar.update', $this->uzgolon), [
            'name' => str_repeat('A', 256),
            'short_description' => str_repeat('B', 5001),
            'start_year' => 1916,
            'status' => 'published',
        ]);

        $response->assertSessionHasErrors([
            'name' => "Qo'zg'olon nomi 255 belgidan oshmasligi kerak.",
            'short_description' => "Qisqa tavsif 5000 belgidan oshmasligi kerak.",
        ]);
    }

    public function test_admin_layout_displays_error_alert_when_errors_present(): void
    {
        $bag = new \Illuminate\Support\ViewErrorBag;
        $bag->put('default', new \Illuminate\Support\MessageBag(['name' => ["Qo'zg'olon nomini kiritish majburiy."]]));

        $response = $this->actingAs($this->admin)
            ->withSession(['errors' => $bag])
            ->get(route('admin.qozgolonlar.edit', $this->uzgolon));

        $response->assertOk();
        $response->assertSee("Ma'lumotlarni saqlashda xatolik yuz berdi:", false);
        $response->assertSee('nomini kiritish majburiy');
    }

    public function test_edit_view_does_not_contain_nested_forms(): void
    {
        HistoricalImage::factory()->create([
            'uzgolon_id' => $this->uzgolon->id,
            'file_path' => 'uzgolonlar/gallery/test.jpg',
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.qozgolonlar.edit', $this->uzgolon));
        $response->assertOk();

        $content = $response->getContent();
        // The main update form contains the whole page content up to the submit button
        // An inner form inside it causes browser to close the outer form early
        preg_match('/<form[^>]+qozgolonlar\/' . $this->uzgolon->slug . '[^>]*>(.*?)<\/form>/s', $content, $match);
        $this->assertNotEmpty($match);
        $this->assertStringNotContainsString('<form', $match[1], 'Main form must not contain nested <form> tags!');
    }
}
