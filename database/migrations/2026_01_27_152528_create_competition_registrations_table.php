<?php

use App\Enum\StatusRegistration;
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
        Schema::create('competition_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('competition_id')->constrained('competitions')->onDelete('cascade');
            $table->foreignUuid('verified_by')->nullable()->constrained('admins')->onDelete('set null');

            $table->json('draft_data')->nullable();

            $table->string('nrp')->nullable()->unique(); // Nullable untuk eksternal
            $table->integer('batch')->nullable(); // Angkatan
            $table->string('major')->nullable();

            $table->string('ktm_path')->nullable(); 
            $table->string('id_card_path')->nullable();
            $table->string('payment_proof')->nullable();
            
            $table->enum('status', array_column(StatusRegistration::cases(), 'value'))->default(StatusRegistration::DRAFT->value);
            $table->text('rejection_reason')->nullable();
            
            $table->unique(['user_id', 'competition_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_registrations');
    }
};
