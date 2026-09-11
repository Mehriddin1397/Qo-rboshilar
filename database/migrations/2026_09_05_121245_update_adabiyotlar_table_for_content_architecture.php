<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adabiyotlar', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('type');
            $table->boolean('featured')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('adabiyotlar', function (Blueprint $table) {
            $table->dropColumn(['status', 'featured']);
        });
    }
};
