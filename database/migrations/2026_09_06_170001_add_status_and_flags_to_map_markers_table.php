<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('map_markers', function (Blueprint $table) {
            $table->string('status')->default('published')->after('description');
            $table->boolean('is_primary')->default(false)->after('status');
            $table->unsignedInteger('sort_order')->default(0)->after('is_primary');
            $table->text('description')->nullable()->change();
        });

        // Ilgari Uzgolon::primaryMarker() `oldestOfMany()` orqali "eng birinchi
        // yaratilgan marker"ni oldindan belgilangan holda "asosiy" deb hisoblardi.
        // Endi bu aniq `is_primary` ustuni orqali belgilanadi — mavjud ma'lumot
        // uchun avvalgi xatti-harakat saqlanib qolishi uchun har bir uzgolon_id
        // guruhidagi eng eski (id bo'yicha kichik) marker `is_primary = true`
        // qilib belgilanadi.
        DB::table('map_markers')
            ->whereNotNull('uzgolon_id')
            ->select('uzgolon_id')
            ->distinct()
            ->pluck('uzgolon_id')
            ->each(function ($uzgolonId) {
                $firstId = DB::table('map_markers')
                    ->where('uzgolon_id', $uzgolonId)
                    ->orderBy('id')
                    ->value('id');

                if ($firstId) {
                    DB::table('map_markers')->where('id', $firstId)->update(['is_primary' => true]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('map_markers', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_primary', 'sort_order']);
            $table->text('description')->nullable(false)->change();
        });
    }
};
