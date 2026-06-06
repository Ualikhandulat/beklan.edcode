<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_accesses', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['part_id']);
            $table->dropColumn(['subject_id', 'part_type', 'part_id', 'student_chooses_part']);

            $table->unsignedSmallInteger('attempts_limit')->default(0)->after('question_count');
        });
    }

    public function down(): void
    {
        Schema::table('test_accesses', function (Blueprint $table) {
            $table->dropColumn('attempts_limit');

            $table->foreignId('subject_id')->nullable()->constrained('subjects')->cascadeOnDelete();
            $table->enum('part_type', ['topic', 'nusqa'])->nullable()->after('subject_id');
            $table->foreignId('part_id')->nullable()->constrained('parts')->cascadeOnDelete();
            $table->boolean('student_chooses_part')->default(false)->after('student_chooses_subject');
        });
    }
};
