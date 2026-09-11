<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Faza 12: mavjud `historical_map_layers` (Faza 3) faqat georeferenced RASTER
     * overlay uchun mo'ljallangan edi (`image_path` majburiy, `bounds`). Endi bu
     * jadval ixtiyoriy ravishda GeoJSON VEKTOR qatlamlarni ham qo'llab-quvvatlashi
     * kerak (§15, §21) — shuning uchun:
     *
     * - `image_path` NOT NULL → nullable (vektor-only qatlam rasmga muhtoj emas).
     * - `slug`, `description`, `geojson`, `historical_region_id`, `status` qo'shiladi.
     * - `title`, `period_id`, `opacity`, `bounds`, `is_active`, `sort_order`
     *   o'zgarishsiz qoladi (`title` — spec "name" so'ragan bo'lsa ham, Qorboshi'dagi
     *   `full_name` bilan bir xil qaror: mavjud nom saqlanadi, keraksiz churn yo'q).
     *
     * Jadval hozircha bo'sh (0 qator) bo'lgani uchun `image_path`ni xavfsiz drop+
     * qayta qo'shish orqali nullable qilish mumkin (doctrine/dbal talab qiladigan
     * `->change()`dan qochiladi).
     */
    public function up(): void
    {
        Schema::table('historical_map_layers', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });

        // Diqqat: `historical_region_id` (foreignId->constrained) o'z alohida
        // Schema::table chaqiruvida qo'shiladi — SQLite FK qo'shishda jadvalni qayta
        // quradi (ALTER TABLE ADD CONSTRAINT yo'q), va uni oddiy addColumn()lar bilan
        // bitta Blueprint'ga aralashtirish mavjud ustunlarni (title, period_id va h.k.)
        // yo'qotib qo'yadigan xatoga olib keladi (tekshirilib topildi, pastga qarang).
        Schema::table('historical_map_layers', function (Blueprint $table) {
            $table->string('slug')->unique()->after('title');
            $table->text('description')->nullable()->after('slug');
            $table->string('image_path')->nullable()->after('description');
            $table->json('geojson')->nullable()->after('bounds');
            $table->string('status')->default('draft')->after('is_active');
        });

        Schema::table('historical_map_layers', function (Blueprint $table) {
            $table->foreignId('historical_region_id')->nullable()->after('period_id')
                ->constrained('historical_regions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('historical_map_layers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('historical_region_id');
            $table->dropColumn(['slug', 'description', 'geojson', 'status']);
            $table->dropColumn('image_path');
        });

        Schema::table('historical_map_layers', function (Blueprint $table) {
            $table->string('image_path')->after('period_id');
        });
    }
};
