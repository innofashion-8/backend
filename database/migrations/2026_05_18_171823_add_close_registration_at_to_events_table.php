<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dateTime('close_registration_at')
                  ->nullable()
                  ->after('start_time')
                  ->comment('Batas akhir pendaftaran. NULL = tidak ada batas tutup registrasi.');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('close_registration_at');
        });
    }
};
