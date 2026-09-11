<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Faza 8: `birth_date`/`death_date` (exact `date`) tarixiy shaxslar uchun soxta
     * aniqlik yaratadi — ko'pincha faqat tug'ilgan/vafot yili ma'lum bo'ladi, kun/oy
     * emas. Shu sabab ularni nullable YIL (smallInteger) bilan almashtiramiz.
     * "Faoliyat davri" (`active_from`/`active_to`) ham tabiatan yil-darajasida
     * tushuncha (masalan 1918-1924) — aniq sana emas.
     */
    public function up(): void
    {
        Schema::table('qorboshilar', function (Blueprint $table) {
            $table->dropColumn(['birth_date', 'death_date']);
        });

        Schema::table('qorboshilar', function (Blueprint $table) {
            $table->smallInteger('birth_year')->nullable()->after('biography');
            $table->smallInteger('death_year')->nullable()->after('birth_place');
            $table->smallInteger('active_from_year')->nullable()->after('death_place');
            $table->smallInteger('active_to_year')->nullable()->after('active_from_year');
            $table->string('status')->default('draft')->after('region_id');
            $table->boolean('featured')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('qorboshilar', function (Blueprint $table) {
            $table->dropColumn(['birth_year', 'death_year', 'active_from_year', 'active_to_year', 'status', 'featured']);
        });

        Schema::table('qorboshilar', function (Blueprint $table) {
            $table->date('birth_date')->nullable();
            $table->date('death_date')->nullable();
        });
    }
};
