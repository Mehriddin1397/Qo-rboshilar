<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            // Default 'published': mavjud hududlar hech qachon o'zboshimchalik bilan
            // yashirilib qolmasin — bu maydon faqat admin CRUD uchun, taxonomy sifatida
            // Region qaysi yozuvlarga biriktirilishini cheklamaydi.
            $table->string('status')->default('published')->after('description');
            $table->unsignedInteger('sort_order')->default(0)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn(['status', 'sort_order']);
        });
    }
};
