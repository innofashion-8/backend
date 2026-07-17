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
        Schema::create('rsvp_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->uuidMorphs('rsvpsable'); // Ini akan membuat kolom 'rsvpsable_type' dan 'rsvpsable_id'
            $table->integer('max_tickets_per_user')->default(1);
            $table->integer('total_quota');
            $table->boolean('is_active')->default(true);
            $table->timestamp('rsvp_open_at')->nullable();
            $table->timestamp('rsvp_close_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rsvp_sessions');
    }
};
