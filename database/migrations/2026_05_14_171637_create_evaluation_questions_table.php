<?php

use App\Enum\QuestionType;
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
        Schema::create('evaluation_questions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('event_id')->constrained('events')->onDelete('cascade');

            $table->unsignedInteger('sort_order')->default(0);
            $table->integer('page_number')->default(1);
            $table->string('header_title')->nullable();
            $table->text('header_description')->nullable();

            $table->text('question_text');
            $table->enum('type', ['header', 'rating', 'text', 'multiple_choice']);
            $table->json('options')->nullable();
            $table->boolean('is_required')->default(true);

            $table->index(['event_id', 'sort_order']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluation_questions');
    }
};
