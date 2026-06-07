<?php

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_accesses', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // TestAccessType enum: 'ent' | 'subject'

            // Target: one of user or group must be set
            $table->foreignIdFor(User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Group::class)->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('is_active')->default(true);

            // ENT: student picks own elective subjects
            $table->boolean('student_chooses_subject')->default(false);

            // ENT: нұсқа configuration (global for all 5 subjects)
            $table->unsignedSmallInteger('nusqa_number')->nullable();
            $table->boolean('student_chooses_nusqa')->default(false);

            // Limits (0 = unlimited for question_count; 0 = unlimited for attempts_limit)
            $table->unsignedSmallInteger('question_count')->default(0);
            $table->unsignedSmallInteger('attempts_limit')->default(1);

            $table->timestamp('expires_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['group_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_accesses');
    }
};
