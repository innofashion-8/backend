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
        Schema::create('user_rsvps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignUuid('rsvp_session_id')->constrained('rsvp_sessions')->onDelete('cascade');
            $table->foreignUuid('verified_by')->nullable()->constrained('admins')->onDelete('set null');
            $table->enum('status', array_column(StatusRegistration::cases(), 'value'))->default(StatusRegistration::DRAFT->value);
            $table->text('rejection_reason')->nullable();
            $table->unique(['user_id', 'rsvp_session_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_rsvps');
    }
};
