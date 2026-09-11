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
        Schema::create('adabiyotlar', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->integer('publication_year')->nullable();
            $table->string('isbn')->nullable();
            $table->string('type');
            $table->string('language')->nullable();
            $table->text('description');
            $table->string('cover_path')->nullable();
            $table->string('source_url')->nullable();
            $table->string('file_path')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('og_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adabiyotlar');
    }
};
