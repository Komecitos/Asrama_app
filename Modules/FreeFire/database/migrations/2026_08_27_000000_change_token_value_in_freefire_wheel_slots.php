<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE freefire_wheel_slots MODIFY COLUMN token_value VARCHAR(50) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE freefire_wheel_slots MODIFY COLUMN token_value INT NULL');
    }
};

