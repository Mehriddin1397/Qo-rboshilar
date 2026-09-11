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
        Schema::create('historical_map_layers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('period_id')->nullable()->constrained('periods')->nullOnDelete();
            $table->string('image_path');
            $table->float('opacity')->default(0.7);
            $table->json('bounds')->nullable();
            $table->boolean('is_active')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_map_layers');
    }
};
