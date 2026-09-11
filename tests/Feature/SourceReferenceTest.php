<?php

namespace Tests\Feature;

use App\Models\HistoricalRegion;
use App\Models\Literature;
use App\Models\SourceReference;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SourceReferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_any_admin_source_reference_route(): void
    {
        $source = SourceReference::factory()->create();

        $this->get('/admin/source-references')->assertRedirect(route('login'));
        $this->get('/admin/source-references/create')->assertRedirect(route('login'));
        $this->get("/admin/source-references/{$source->id}")->assertRedirect(route('login'));
        $this->get("/admin/source-references/{$source->id}/edit")->assertRedirect(route('login'));
        $this->delete("/admin/source-references/{$source->id}")->assertRedirect(route('login'));
    }

    public function test_plain_user_cannot_access_admin_source_reference_routes(): void
    {
        $user = User::factory()->create();
        $source = SourceReference::factory()->create();

        $this->actingAs($user)->get('/admin/source-references')->assertForbidden();
        $this->actingAs($user)->get('/admin/source-references/create')->assertForbidden();
        $this->actingAs($user)->get("/admin/source-references/{$source->id}/edit")->assertForbidden();
    }

    public function test_editor_cannot_access_admin_source_reference_routes(): void
    {
        $editor = User::factory()->editor()->create();

        $this->actingAs($editor)->get('/admin/source-references')->assertForbidden();
    }

    public function test_admin_can_perform_full_crud(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/source-references')->assertOk();
        $this->actingAs($admin)->get('/admin/source-references/create')->assertOk();

        $createResponse = $this->actingAs($admin)->post('/admin/source-references', [
            'author' => '[DEMO] Test muallif',
            'title' => '[DEMO DATA] Test manba sarlavhasi',
            'source_type' => 'book',
            'year' => 1913,
        ]);

        $createResponse->assertRedirect(route('admin.source-references.index'));
        $this->assertDatabaseHas('source_references', ['title' => '[DEMO DATA] Test manba sarlavhasi', 'year' => 1913]);

        $source = SourceReference::where('title', '[DEMO DATA] Test manba sarlavhasi')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.source-references.show', $source))->assertOk();
        $this->actingAs($admin)->get(route('admin.source-references.edit', $source))->assertOk();

        $updateResponse = $this->actingAs($admin)->put(route('admin.source-references.update', $source), [
            'author' => '[DEMO] Yangilangan muallif',
            'title' => '[DEMO DATA] Yangilangan sarlavha',
            'source_type' => 'archive',
        ]);

        $updateResponse->assertRedirect(route('admin.source-references.index'));
        $this->assertDatabaseHas('source_references', [
            'id' => $source->id,
            'title' => '[DEMO DATA] Yangilangan sarlavha',
            'source_type' => 'archive',
        ]);

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.source-references.destroy', $source->fresh()));
        $deleteResponse->assertRedirect(route('admin.source-references.index'));
        $this->assertDatabaseMissing('source_references', ['id' => $source->id]);
    }

    public function test_author_and_title_are_required(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/source-references', ['source_type' => 'book']);

        $response->assertSessionHasErrors(['author', 'title']);
    }

    public function test_invalid_source_type_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/source-references', [
            'author' => '[DEMO] Muallif',
            'title' => '[DEMO DATA] Sarlavha',
            'source_type' => 'not-a-real-type',
        ]);

        $response->assertSessionHasErrors('source_type');
    }

    public function test_invalid_url_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/source-references', [
            'author' => '[DEMO] Muallif',
            'title' => '[DEMO DATA] Sarlavha',
            'source_type' => 'website',
            'url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors('url');
    }

    public function test_literature_must_exist(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/source-references', [
            'author' => '[DEMO] Muallif',
            'title' => '[DEMO DATA] Sarlavha',
            'source_type' => 'book',
            'literature_id' => 9999,
        ]);

        $response->assertSessionHasErrors('literature_id');
    }

    /**
     * Bu test aynan foydalanuvchi so'ragan senariyni tekshiradi: admin panelda
     * yaratilgan Literature yozuvi uchun SourceReference yaratib, uni shu
     * Literature'ga bog'lash — va shu SourceReference endi HistoricalRegion'ning
     * "Manbalar" ro'yxatida ishlatilishi mumkinligini tasdiqlaydi.
     */
    public function test_source_reference_can_be_linked_to_a_literature_record_and_then_attached_to_a_historical_region(): void
    {
        $admin = User::factory()->admin()->create();
        $literature = Literature::factory()->create([
            'title' => '[DEMO] Rossiya imperiyasi. Geografik tavsif',
            'publication_year' => 1913,
        ]);

        $createResponse = $this->actingAs($admin)->post('/admin/source-references', [
            'author' => '[DEMO] Rossiya imperiyasi statistika qo\'mitasi',
            'title' => '[DEMO DATA] Geografik tavsif — guberniya va viloyatlar bo\'yicha',
            'source_type' => 'book',
            'year' => 1913,
            'literature_id' => $literature->id,
        ]);

        $createResponse->assertRedirect(route('admin.source-references.index'));

        $source = SourceReference::where('literature_id', $literature->id)->firstOrFail();
        $this->assertSame($literature->id, $source->literature_id);

        $historicalRegion = HistoricalRegion::factory()->create();

        $updateResponse = $this->actingAs($admin)->put(route('admin.historical-regions.update', $historicalRegion), [
            'name' => $historicalRegion->name,
            'region_type' => 'other',
            'status' => 'draft',
            'accuracy_status' => 'uncertain',
            'source_reference_ids' => [$source->id],
        ]);

        $updateResponse->assertRedirect();
        $this->assertTrue($historicalRegion->fresh()->sourceReferences->contains('id', $source->id));
    }

    public function test_deleting_source_reference_attached_to_historical_region_is_blocked(): void
    {
        $admin = User::factory()->admin()->create();
        $source = SourceReference::factory()->create();
        $historicalRegion = HistoricalRegion::factory()->create();
        $historicalRegion->sourceReferences()->attach($source->id);

        $response = $this->actingAs($admin)->delete(route('admin.source-references.destroy', $source));

        $response->assertRedirect(route('admin.source-references.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('source_references', ['id' => $source->id]);
    }

    public function test_deleting_unattached_source_reference_succeeds(): void
    {
        $admin = User::factory()->admin()->create();
        $source = SourceReference::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.source-references.destroy', $source));

        $response->assertRedirect(route('admin.source-references.index'));
        $this->assertDatabaseMissing('source_references', ['id' => $source->id]);
    }

    public function test_index_eager_loads_literature_without_n_plus_one(): void
    {
        $admin = User::factory()->admin()->create();

        SourceReference::factory()->count(3)->create();

        DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/source-references');
        $smallCount = count(DB::getQueryLog());
        DB::disableQueryLog();
        DB::flushQueryLog();

        SourceReference::factory()->count(12)->create();

        DB::enableQueryLog();
        $this->actingAs($admin)->get('/admin/source-references');
        $largeCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertSame($smallCount, $largeCount);
    }

    public function test_demo_source_references_from_seeder_are_untouched(): void
    {
        $this->seed(\Database\Seeders\DemoSeeder::class);

        $this->assertDatabaseHas('source_references', ['author' => '[DEMO] X. Alimov']);
    }
}
