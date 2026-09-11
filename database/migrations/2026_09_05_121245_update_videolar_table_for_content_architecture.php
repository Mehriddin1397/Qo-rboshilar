<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videolar', function (Blueprint $table) {
            $table->string('youtube_id', 32)->nullable()->after('youtube_url');
            $table->string('status')->default('draft')->after('category');
            $table->boolean('featured')->default(false)->after('status');
            $table->foreignId('literature_id')->nullable()->after('uzgolon_id')
                ->constrained('adabiyotlar')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('videolar', function (Blueprint $table) {
            $table->dropConstrainedForeignId('literature_id');
            $table->dropColumn(['youtube_id', 'status', 'featured']);
        });
    }
};
