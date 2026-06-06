<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove flat subject columns — per-subject config lives in test_access_subjects
        Schema::table('test_accesses', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['elective_subject1_id']);
            $table->dropForeign(['elective_subject2_id']);
            $table->dropForeign(['part_id']);
            $table->dropColumn([
                'subject_id', 'elective_subject1_id', 'elective_subject2_id',
                'part_type', 'part_id', 'student_chooses_part',
            ]);
        });

        Schema::create('test_access_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_access_id')->constrained('test_accesses')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();

            // Per-subject part config
            $table->enum('part_type', ['topic', 'nusqa'])->nullable();
            $table->foreignId('part_id')->nullable()->constrained('parts')->cascadeOnDelete();
            $table->boolean('student_chooses_part')->default(false);

            $table->timestamps();
            $table->index('test_access_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_access_subjects');

        Schema::table('test_accesses', function (Blueprint $table) {
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->cascadeOnDelete()->after('group_id');
            $table->foreignId('elective_subject1_id')->nullable()->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('elective_subject2_id')->nullable()->constrained('subjects')->cascadeOnDelete();
            $table->enum('part_type', ['topic', 'nusqa'])->nullable();
            $table->foreignId('part_id')->nullable()->constrained('parts')->cascadeOnDelete();
            $table->boolean('student_chooses_part')->default(false);
        });
    }
};
