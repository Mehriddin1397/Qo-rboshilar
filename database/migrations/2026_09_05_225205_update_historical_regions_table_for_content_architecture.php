<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Faza 12: Qorboshi/Uzgolon/Literature/Video/Blog'da qo'llangan bir xil naqsh —
     * `status` (draft/published — faqat published public xaritada ko'rinadi),
     * `featured` (kelajakda homepage/map highlight uchun) va `sort_order` (xaritada
     * ko'rsatish tartibi, §14) qo'shiladi. Mavjud fieldlar (name/slug/historical_name/
     * modern_name/description/geojson/period_id/region_id) o'zgarishsiz qoladi.
     */
    public function up(): void
    {
        Schema::table('historical_regions', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('geojson');
            $table->boolean('featured')->default(false)->after('status');
            $table->integer('sort_order')->default(0)->after('featured');
        });
    }

    public function down(): void
    {
        Schema::table('historical_regions', function (Blueprint $table) {
            $table->dropColumn(['status', 'featured', 'sort_order']);
        });
    }
};
