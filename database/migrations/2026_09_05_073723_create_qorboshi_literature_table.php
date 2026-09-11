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
        Schema::create('qorboshi_literature', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qorboshi_id')->constrained('qorboshilar')->cascadeOnDelete();
            $table->foreignId('literature_id')->constrained('adabiyotlar')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['qorboshi_id', 'literature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qorboshi_literature');
    }
};
