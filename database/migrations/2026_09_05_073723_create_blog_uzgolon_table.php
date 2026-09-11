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
        Schema::create('blog_uzgolon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('bloglar')->cascadeOnDelete();
            $table->foreignId('uzgolon_id')->constrained('uzgolonlar')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['blog_id', 'uzgolon_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_uzgolon');
    }
};
