<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Faza 14 §4: TimelineEvent'ni real ishlaydigan content entityga aylantiradi —
     * Qorboshi/Uzgolon'da Faza 8-9'da qo'llangan bir xil tamoyil.
     *
     * - `date_from`/`date_to` (`date`) OLIB TASHLANADI — bular soxta kun/oy
     *   aniqligini majbur qilardi (masalan faqat "1916" ma'lum bo'lsa ham
     *   "1916-01-01" kabi to'liq sana kiritishga majburlardi). O'rniga:
     *   `start_year`/`end_year` (yil darajasi, asosiy holat) + ixtiyoriy
     *   `event_date` (faqat aniq sana haqiqatan ma'lum bo'lganda).
     * - `year` (NOT NULL) → `start_year`ga nomlanadi (ma'lumot yo'qolmaydi,
     *   Uzgolon'da Faza 9'da qo'llangan bir xil pattern).
     * - `slug`, `status`, `accuracy_status`, `featured` — boshqa content
     *   turlari bilan bir xil naqsh.
     * - `period_id`, `historical_region_id` — Faza 12-13 arxitekturasiga bog'lash.
     * - `latitude`/`longitude` — xarita bilan sinxronizatsiya uchun (Faza 14 §12).
     *
     * Jadval hozircha bo'sh (0 qator) — xavfsiz.
     */
    public function up(): void
    {
        Schema::table('timeline_events', function (Blueprint $table) {
            $table->dropColumn(['date_from', 'date_to']);
        });

        Schema::table('timeline_events', function (Blueprint $table) {
            $table->renameColumn('year', 'start_year');
        });

        Schema::table('timeline_events', function (Blueprint $table) {
            $table->string('slug')->unique()->after('title');
            $table->smallInteger('end_year')->nullable()->after('start_year');
            $table->date('event_date')->nullable()->after('end_year');
            $table->string('status')->default('draft')->after('description');
            $table->string('accuracy_status')->default('uncertain')->after('status');
            $table->boolean('featured')->default(false)->after('accuracy_status');
            $table->decimal('latitude', 10, 7)->nullable()->after('sort_order');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        // FK ustunlari alohida Schema::table chaqiruvida — Faza 12'da SQLite'da
        // foreignId()->constrained()'ni oddiy addColumn()lar bilan bitta Blueprint'ga
        // aralashtirish --pretend preview'ida chalkash natija berishi kuzatilgan edi
        // (haqiqiy ijroda muammo emasligi tekshirilgan, lekin ehtiyot chorasi sifatida
        // shu naqsh davom ettiriladi).
        Schema::table('timeline_events', function (Blueprint $table) {
            $table->foreignId('period_id')->nullable()->after('region_id')->constrained('periods')->nullOnDelete();
        });

        Schema::table('timeline_events', function (Blueprint $table) {
            $table->foreignId('historical_region_id')->nullable()->after('period_id')->constrained('historical_regions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('timeline_events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('historical_region_id');
            $table->dropConstrainedForeignId('period_id');
            $table->dropColumn(['slug', 'end_year', 'event_date', 'status', 'accuracy_status', 'featured', 'latitude', 'longitude']);
        });

        Schema::table('timeline_events', function (Blueprint $table) {
            $table->renameColumn('start_year', 'year');
        });

        Schema::table('timeline_events', function (Blueprint $table) {
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
        });
    }
};
