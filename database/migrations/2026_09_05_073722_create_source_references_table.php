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
        Schema::create('source_references', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            $table->string('title');
            $table->string('publisher')->nullable();
            $table->integer('year')->nullable();
            $table->string('url')->nullable();
            $table->string('page')->nullable();
            $table->text('note')->nullable();
            $table->string('source_type');
            $table->foreignId('literature_id')->nullable()->constrained('adabiyotlar')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('source_references');
    }
};
