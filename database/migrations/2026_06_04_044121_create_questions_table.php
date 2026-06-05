<?php

use App\Models\Part;
use App\Models\Subject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Subject::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Part::class)->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->integer('count_variants')->default(0);
            $table->integer('count_answers')->default(0);
            $table->longText('text')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->index('subject_id');
            $table->index('part_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
