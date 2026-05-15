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
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn('attended');
            $table->enum('attended_status', array_column(AttendedStatus::cases(), 'value'))
                  ->default(AttendedStatus::PENDING->value);
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropColumn('check_out_at');
            $table->dropColumn('check_in_at');
            $table->dropColumn('attended_status');
            $table->boolean('attended')->default(false);
        });
    }
};
