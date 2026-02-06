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
        Schema::create('event_registrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignUuid('verified_by')->nullable()->constrained('admins')->onDelete('set null');

            $table->json('draft_data')->nullable();

            // $table->string('nrp')->nullable()->unique(); // Nullable buat External
            // $table->string('major')->nullable();
            
            $table->string('payment_proof')->nullable();

            $table->enum('status', array_column(StatusRegistration::cases(), 'value'))->default(StatusRegistration::DRAFT->value);
            $table->text('rejection_reason')->nullable();
            
            $table->boolean('attended')->default(false);

            $table->unique(['user_id', 'event_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_registrations');
    }
};
