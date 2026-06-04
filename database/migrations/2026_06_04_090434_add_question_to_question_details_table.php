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
        Schema::table('question_details', function (Blueprint $table) {
            $table->longText('question')->nullable()->default(null)->after('question_id');
        });
    }

    public function down(): void
    {
        Schema::table('question_details', function (Blueprint $table) {
            $table->dropColumn('question');
        });
    }
};
