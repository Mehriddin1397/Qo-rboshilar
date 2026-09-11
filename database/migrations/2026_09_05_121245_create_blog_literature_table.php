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
        Schema::create('blog_literature', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('bloglar')->cascadeOnDelete();
            $table->foreignId('literature_id')->constrained('adabiyotlar')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['blog_id', 'literature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_literature');
    }
};
