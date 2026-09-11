<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Faza 13 §6-7: HistoricalRegion "bu chegara nimani bildiradi" (region_type)
     * va "bu geometriya qanchalik ishonchli" (accuracy_status) metadatasini oladi.
     * HistoricalMapLayer ham accuracy_status oladi (region_type kerak emas — layer
     * o'zi mustaqil chegara emas, region/raster/vektor qatlam). Ikkalasi ham
     * additive, mavjud ma'lumotga (0 qator) ta'sir qilmaydi.
     */
    public function up(): void
    {
        Schema::table('historical_regions', function (Blueprint $table) {
            $table->string('region_type')->default('other')->after('modern_name');
            $table->string('accuracy_status')->default('uncertain')->after('status');
        });

        Schema::table('historical_map_layers', function (Blueprint $table) {
            $table->string('accuracy_status')->default('uncertain')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('historical_regions', function (Blueprint $table) {
            $table->dropColumn(['region_type', 'accuracy_status']);
        });

        Schema::table('historical_map_layers', function (Blueprint $table) {
            $table->dropColumn('accuracy_status');
        });
    }
};
