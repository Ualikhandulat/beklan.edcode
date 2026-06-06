<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the over-engineered pivot table — everything lives in test_accesses
        Schema::dropIfExists('test_access_subjects');

        Schema::table('test_accesses', function (Blueprint $table) {
            // Subject for "subject" type
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->cascadeOnDelete()->after('group_id');

            // ENT: two elective subjects
            $table->foreignId('elective_subject1_id')->nullable()->constrained('subjects')->cascadeOnDelete()->after('subject_id');
            $table->foreignId('elective_subject2_id')->nullable()->constrained('subjects')->cascadeOnDelete()->after('elective_subject1_id');

            // Global part config — one part_type applies to the whole test
            $table->enum('part_type', ['topic', 'nusqa'])->nullable()->after('elective_subject2_id');
            $table->foreignId('part_id')->nullable()->constrained('parts')->cascadeOnDelete()->after('part_type');
            $table->boolean('student_chooses_part')->default(false)->after('student_chooses_subject');
        });
    }

    public function down(): void
    {
        Schema::table('test_accesses', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropForeign(['elective_subject1_id']);
            $table->dropForeign(['elective_subject2_id']);
            $table->dropForeign(['part_id']);
            $table->dropColumn(['subject_id', 'elective_subject1_id', 'elective_subject2_id', 'part_type', 'part_id', 'student_chooses_part']);
        });
    }
};
