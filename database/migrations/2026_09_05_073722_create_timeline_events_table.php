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
        Schema::create('timeline_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('date_from')->nullable();
            $table->date('date_to')->nullable();
            $table->integer('year');
            $table->text('description');
            $table->string('image_path')->nullable();
            $table->foreignId('qorboshi_id')->nullable()->constrained('qorboshilar')->cascadeOnDelete();
            $table->foreignId('uzgolon_id')->nullable()->constrained('uzgolonlar')->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_events');
    }
};
