<?php

use App\Models\Part;
use App\Models\Subject;
use App\Models\TestAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_access_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(TestAccess::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Subject::class)->constrained()->cascadeOnDelete();
            $table->string('part_type')->nullable(); // PartType enum: 'topic' | 'nusqa'
            $table->foreignIdFor(Part::class)->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('student_chooses_part')->default(false);
            $table->timestamps();

            $table->index('test_access_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_access_subjects');
    }
};
