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
        Schema::create('event_tickets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ticket_code')->unique();
            $table->foreignUuid('event_registration_id')->constrained('event_registrations')->onDelete('cascade');
            $table->string('guest_name')->nullable();
            
            $table->enum('attended_status', array_column(\App\Enum\AttendedStatus::cases(), 'value'))
                  ->default(\App\Enum\AttendedStatus::PENDING->value);
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
        Schema::dropIfExists('event_tickets');
    }
};
