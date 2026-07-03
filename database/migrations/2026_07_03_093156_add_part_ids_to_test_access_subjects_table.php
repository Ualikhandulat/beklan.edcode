<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_access_subjects', function (Blueprint $table) {
            // Подмножество нұсқа (id частей), из которых студент выбирает одну при старте.
            // null/пусто = без ограничения (все нұсқа предмета).
            $table->json('part_ids')->nullable()->after('part_id');
        });
    }

    public function down(): void
    {
        Schema::table('test_access_subjects', function (Blueprint $table) {
            $table->dropColumn('part_ids');
        });
    }
};
