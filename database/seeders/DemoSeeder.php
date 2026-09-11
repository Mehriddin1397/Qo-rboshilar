<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Comment;
use App\Models\HistoricalRegion;
use App\Models\Literature;
use App\Models\Period;
use App\Models\Qorboshi;
use App\Models\Region;
use App\Models\SourceReference;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Models\Uzgolon;
use App\Models\Video;
use Illuminate\Database\Seeder;

/**
 * Faqat DEMO ma'lumot — real tarixiy fakt emas. Loyihani jonli ko'rsatish uchun.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin Foydalanuvchi',
            'email' => 'admin@example.com',
        ]);
        $editor = User::factory()->editor()->create([
            'name' => 'Muharrir',
            'email' => 'editor@example.com',
        ]);
        $user = User::factory()->create([
            'name' => 'Oddiy Foydalanuvchi',
            'email' => 'user@example.com',
        ]);

        $region = Region::factory()->create(['name' => "[DEMO] Farg'ona vodiysi"]);
        $period = Period::factory()->create(['name' => '[DEMO] 1918-1924 davri', 'start_year' => 1918, 'end_year' => 1924]);

        $qorboshi = Qorboshi::factory()->published()->featured()->create([
            'full_name' => '[DEMO] Madaminbek',
            'region_id' => $region->id,
        ]);
        Qorboshi::factory()->published()->count(3)->create(['region_id' => $region->id]);

        $uzgolon = Uzgolon::factory()->published()->featured()->create([
            'name' => "[DEMO] Farg'ona qo'zg'oloni",
            'region_id' => $region->id,
            'period_id' => $period->id,
        ]);
        Uzgolon::factory()->published()->count(2)->create(['region_id' => $region->id, 'period_id' => $period->id]);

        $qorboshi->uzgolonlar()->attach($uzgolon->id);

        Literature::factory()->published()->count(3)->create();
        Video::factory()->published()->count(2)->create();

        Blog::factory()->approved()->create(['author_id' => $user->id, 'title' => '[DEMO] Tarixiy voqealar haqida']);
        Blog::factory()->approved()->count(2)->create(['author_id' => $editor->id]);

        Comment::factory()->approved()->for($user, 'author')->for($qorboshi, 'commentable')->create([
            'content' => 'Juda qiziqarli maqola, rahmat! [DEMO]',
        ]);
        Comment::factory()->approved()->for($editor, 'author')->for($qorboshi, 'commentable')->create([
            'content' => "Qo'shimcha manbalar ham bo'lsa yaxshi bo'lardi. [DEMO]",
        ]);
        Comment::factory()->for($user, 'author')->for($uzgolon, 'commentable')->create([
            'content' => 'Moderatsiyada turgan izoh namunasi. [DEMO]',
        ]);

        $source = SourceReference::factory()->create([
            'author' => '[DEMO] X. Alimov',
            'title' => '[DEMO] Turkiston tarixi ocherklari',
        ]);
        $historicalRegion = HistoricalRegion::factory()->published()->create([
            'name' => "[DEMO] Farg'ona tarixiy hududi",
            'region_id' => $region->id,
            'period_id' => $period->id,
            'geojson' => [
                'type' => 'Polygon',
                'coordinates' => [[[70.0, 40.0], [72.5, 40.0], [72.5, 41.0], [70.0, 41.0], [70.0, 40.0]]],
            ],
        ]);
        $historicalRegion->sourceReferences()->attach($source->id);

        $event = TimelineEvent::factory()->published()->featured()->withCoordinates()->create([
            'title' => "[DEMO] Farg'ona qo'zg'oloni boshlanishi",
            'description' => "[DEMO DATA] Xronologiya voqeasi namunasi — Farg'ona qo'zg'oloni bilan bog'liq.",
            'start_year' => 1918,
            'end_year' => 1924,
            'period_id' => $period->id,
            'qorboshi_id' => $qorboshi->id,
            'uzgolon_id' => $uzgolon->id,
            'historical_region_id' => $historicalRegion->id,
        ]);
        $event->sourceReferences()->attach($source->id);

        TimelineEvent::factory()->published()->create([
            'title' => '[DEMO] Boshqa xronologiya voqeasi',
            'period_id' => $period->id,
        ]);

        $this->command?->info('Demo data seeded.');
    }
}
