<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Faza 9: Qorboshi'da qo'llangan bir xil tamoyil — `start_date`/`end_date` (`date`)
     * soxta kun/oy aniqligini talab qilardi, holbuki qo'zg'olonlar odatda faqat yil
     * darajasida ma'lum (masalan 1918-1924). Mavjud `year` ustuni "boshlanish yili"
     * ma'nosiga to'g'ri kelgani uchun `start_year`ga nomlanadi (ma'lumot yo'qolmaydi),
     * `end_year` esa yangi qo'shiladi — natijada davr oralig'ini to'g'ri ifodalaydi.
     */
    public function up(): void
    {
        Schema::table('uzgolonlar', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });

        Schema::table('uzgolonlar', function (Blueprint $table) {
            $table->renameColumn('year', 'start_year');
        });

        Schema::table('uzgolonlar', function (Blueprint $table) {
            $table->smallInteger('end_year')->nullable()->after('start_year');
            $table->string('historical_location')->nullable()->after('region_id');
            $table->string('modern_location')->nullable()->after('historical_location');
            $table->string('status')->default('draft')->after('modern_location');
            $table->boolean('featured')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('uzgolonlar', function (Blueprint $table) {
            $table->dropColumn(['end_year', 'historical_location', 'modern_location', 'status', 'featured']);
        });

        Schema::table('uzgolonlar', function (Blueprint $table) {
            $table->renameColumn('start_year', 'year');
        });

        Schema::table('uzgolonlar', function (Blueprint $table) {
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
        });
    }
};
