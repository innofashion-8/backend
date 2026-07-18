<?php

use App\Enum\AttendedStatus;
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
        Schema::create('tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ticket_code')->unique()->nullable(); // Contoh: X7B9Q2
            $table->foreignUuid('user_rsvp_id')->constrained('user_rsvps')->onDelete('cascade');
            $table->string('guest_name');
            $table->enum('attended_status', array_column(AttendedStatus::cases(), 'value'))
                  ->default(AttendedStatus::PENDING->value);
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
