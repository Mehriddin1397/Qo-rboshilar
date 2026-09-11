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
        Schema::create('uzgolon_literature', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uzgolon_id')->constrained('uzgolonlar')->cascadeOnDelete();
            $table->foreignId('literature_id')->constrained('adabiyotlar')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['uzgolon_id', 'literature_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uzgolon_literature');
    }
};
