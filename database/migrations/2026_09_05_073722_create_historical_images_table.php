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
        Schema::create('historical_images', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('file_path');
            $table->text('caption')->nullable();
            $table->string('source')->nullable();
            $table->string('source_url')->nullable();
            $table->string('copyright')->nullable();
            $table->integer('year')->nullable();
            $table->string('alt_text')->nullable();
            $table->foreignId('qorboshi_id')->nullable()->constrained('qorboshilar')->cascadeOnDelete();
            $table->foreignId('uzgolon_id')->nullable()->constrained('uzgolonlar')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_images');
    }
};
