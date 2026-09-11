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
        Schema::create('videolar', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('youtube_url');
            $table->string('category');
            $table->integer('duration_seconds')->nullable();
            $table->text('description')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->foreignId('qorboshi_id')->nullable()->constrained('qorboshilar')->nullOnDelete();
            $table->foreignId('uzgolon_id')->nullable()->constrained('uzgolonlar')->nullOnDelete();
            $table->text('sources')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videolar');
    }
};
