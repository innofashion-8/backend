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
        Schema::create('evaluation_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('evaluation_id')
                  ->constrained('evaluations')
                  ->onDelete('cascade');
            $table->foreignUuid('evaluation_question_id')
                  ->constrained('evaluation_questions')
                  ->onDelete('cascade');
            $table->text('answer_value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_answers');
    }
};
