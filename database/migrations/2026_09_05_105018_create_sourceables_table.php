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
        Schema::create('sourceables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_reference_id')->constrained('source_references')->cascadeOnDelete();
            $table->string('sourceable_type');
            $table->unsignedBigInteger('sourceable_id');
            $table->timestamps();

            $table->index(['sourceable_type', 'sourceable_id']);
            $table->unique(['source_reference_id', 'sourceable_type', 'sourceable_id'], 'sourceables_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sourceables');
    }
};
