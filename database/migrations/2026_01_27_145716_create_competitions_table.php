<?php

use App\Enum\ParticipantType;
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
        Schema::create('competitions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            // $table->enum('category', array_column(CompetitionCategory::cases(), 'value'))->default(CompetitionCategory::INTERMEDIATE->value);
            $table->enum('participant_type', array_column(ParticipantType::cases(), 'value'))->default(ParticipantType::INDIVIDUAL->value);
            $table->integer('min_members')->nullable();
            $table->integer('max_members')->nullable();
            $table->string('wa_link_international');
            $table->string('wa_link_national');
            $table->text('description')->nullable();
            // $table->decimal('registration_fee', 8, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('registration_open_at');
            $table->timestamp('registration_close_at');
            $table->timestamp('submission_open_at');
            $table->timestamp('submission_close_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
