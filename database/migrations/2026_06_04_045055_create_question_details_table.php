<?php

use App\Models\Question;
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
        Schema::create('question_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Question::class)->constrained();
            $table->longText('question')->nullable()->default(null);

            $table->longText('var1');
            $table->longText('var2');
            $table->longText('var3')->nullable()->default(null);
            $table->longText('var4')->nullable()->default(null);
            $table->longText('var5')->nullable()->default(null);
            $table->longText('var6')->nullable()->default(null);
            $table->longText('var7')->nullable()->default(null);
            $table->longText('var8')->nullable()->default(null);
            $table->longText('var9')->nullable()->default(null);
            $table->longText('var10')->nullable()->default(null);

            $table->json('answers');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_details');
    }
};
