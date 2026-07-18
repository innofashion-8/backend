<?php

use App\Enum\UserType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN `type` VARCHAR(50) NOT NULL DEFAULT 'EXTERNAL'");
    }

    public function down(): void
    {
        $enumValues = implode("','", array_column(UserType::cases(), 'value'));

        DB::statement("ALTER TABLE users MODIFY COLUMN `type` ENUM('{$enumValues}') NOT NULL DEFAULT 'EXTERNAL'");
    }
};

