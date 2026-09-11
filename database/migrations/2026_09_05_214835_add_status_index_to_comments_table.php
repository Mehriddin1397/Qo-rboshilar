<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Faza 11 §50: public sahifalarda "shu commentable uchun faqat approved
     * commentlar" so'rovi doim uch ustun bo'yicha filtrlanadi — mavjud 2 ustunli
     * index buni to'liq qamramaydi, shuning uchun status qo'shilgan composite
     * index bilan almashtiriladi (eskisi drop qilinib, kengaytirilgani qo'shiladi).
     */
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['commentable_type', 'commentable_id']);
            $table->index(['commentable_type', 'commentable_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['commentable_type', 'commentable_id', 'status']);
            $table->index(['commentable_type', 'commentable_id']);
        });
    }
};
