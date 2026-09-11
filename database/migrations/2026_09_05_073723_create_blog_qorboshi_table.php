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
        Schema::create('blog_qorboshi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('bloglar')->cascadeOnDelete();
            $table->foreignId('qorboshi_id')->constrained('qorboshilar')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['blog_id', 'qorboshi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_qorboshi');
    }
};
