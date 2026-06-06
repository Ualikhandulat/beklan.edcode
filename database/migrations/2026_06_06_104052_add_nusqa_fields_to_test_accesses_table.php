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
        Schema::table('test_accesses', function (Blueprint $table) {
            $table->unsignedSmallInteger('nusqa_number')->nullable()->after('student_chooses_subject');
            $table->boolean('student_chooses_nusqa')->default(false)->after('nusqa_number');
        });
    }

    public function down(): void
    {
        Schema::table('test_accesses', function (Blueprint $table) {
            $table->dropColumn(['nusqa_number', 'student_chooses_nusqa']);
        });
    }
};
