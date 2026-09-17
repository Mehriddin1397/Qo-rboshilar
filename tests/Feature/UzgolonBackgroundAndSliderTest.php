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

class UzgolonBackgroundAndSliderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_admin_can_upload_background_image_for_uzgolon(): void
    {
        $admin = User::factory()->admin()->create();
        $region = Region::create(['name' => 'Fargʻona', 'slug' => 'fargona']);
        $period = Period::create(['name' => 'Davr 1', 'slug' => 'davr-1', 'start_year' => 1918, 'end_year' => 1924]);

        $background = UploadedFile::fake()->image('header-bg.jpg', 1920, 600);
        $cover = UploadedFile::fake()->image('cover.jpg', 800, 600);

        $response = $this->actingAs($admin)->post(route('admin.qozgolonlar.store'), [
            'name' => "Farg'ona qo'zg'oloni",
            'slug' => 'fargona-qozgoloni',
            'short_description' => "Farg'onadagi tarixiy qo'zg'olon tavsifi",
            'start_year' => 1918,
            'end_year' => 1922,
            'status' => 'published',
            'region_id' => $region->id,
            'period_id' => $period->id,
            'cover' => $cover,
            'background' => $background,
        ]);

        $response->assertRedirect(route('admin.qozgolonlar.index'));

        $uzgolon = Uzgolon::where('slug', 'fargona-qozgoloni')->firstOrFail();
        $this->assertNotNull($uzgolon->background_image);
        $this->assertNotNull($uzgolon->backgroundImageUrl());
        Storage::disk('public')->assertExists($uzgolon->background_image);
    }

    public function test_admin_can_remove_background_image(): void
    {
        $admin = User::factory()->admin()->create();
        $uzgolon = Uzgolon::factory()->published()->create([
            'background_image' => 'uzgolonlar/backgrounds/old-bg.jpg',
        ]);
        Storage::disk('public')->put('uzgolonlar/backgrounds/old-bg.jpg', 'fake-image-content');

        $response = $this->actingAs($admin)->put(route('admin.qozgolonlar.update', $uzgolon), [
            'name' => $uzgolon->name,
            'slug' => $uzgolon->slug,
            'short_description' => $uzgolon->short_description,
            'start_year' => $uzgolon->start_year,
            'end_year' => $uzgolon->end_year,
            'status' => $uzgolon->status->value,
            'remove_background' => '1',
        ]);

        $response->assertRedirect(route('admin.qozgolonlar.index'));
        $uzgolon->refresh();

        $this->assertNull($uzgolon->background_image);
        $this->assertNull($uzgolon->backgroundImageUrl());
        Storage::disk('public')->assertMissing('uzgolonlar/backgrounds/old-bg.jpg');
    }

    public function test_public_show_page_renders_hero_background_and_image_slider(): void
    {
        $uzgolon = Uzgolon::factory()->published()->create([
            'background_image' => 'uzgolonlar/backgrounds/fargona-bg.jpg',
        ]);
        Storage::disk('public')->put('uzgolonlar/backgrounds/fargona-bg.jpg', 'bg');

        // Tarixiy suratlar yaratamiz
        HistoricalImage::create([
            'uzgolon_id' => $uzgolon->id,
            'file_path' => 'uzgolonlar/gallery/photo1.jpg',
            'caption' => '1919-yilgi tarixiy sarkardalar yigʻini',
            'year' => 1919,
            'source' => 'Oʻzbekiston Milliy Arxivi',
        ]);
        HistoricalImage::create([
            'uzgolon_id' => $uzgolon->id,
            'file_path' => 'uzgolonlar/gallery/photo2.jpg',
            'caption' => 'Qoʻzgʻolonchilar qarorgohi',
            'year' => 1920,
        ]);

        $response = $this->get(route('qozgolonlar.show', $uzgolon));

        $response->assertOk();
        // Fon rasmi mavjudligini tekshirish
        $response->assertSee('uzgolonlar/backgrounds/fargona-bg.jpg');
        // Slider elementlari va caption'lar chiqishini tekshirish
        $response->assertSee('Tarixiy suratlar');
        $response->assertSee('1919-yilgi tarixiy sarkardalar yigʻini');
        $response->assertSee('Qoʻzgʻolonchilar qarorgohi');
        $response->assertSee('Oʻzbekiston Milliy Arxivi');
        $response->assertSee('startAutoplay()', false);
        $response->assertSee('Kattalashtirish');
    }
}
