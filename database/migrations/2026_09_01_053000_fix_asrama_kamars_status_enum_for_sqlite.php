<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // In SQLite, enum generates a hard CHECK constraint.
            // We recreate the table cleanly preserving all existing data.
            DB::statement("CREATE TABLE asrama_kamars_temp (
                id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                nomor_kamar VARCHAR(50) NOT NULL,
                lantai INTEGER DEFAULT 1 NOT NULL,
                kapasitas INTEGER DEFAULT 1 NOT NULL,
                harga_per_bulan INTEGER DEFAULT 0 NOT NULL,
                status VARCHAR(50) DEFAULT 'Tersedia' NOT NULL,
                fasilitas TEXT NULL,
                catatan TEXT NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            );");

            // Copy all existing records
            DB::statement("INSERT INTO asrama_kamars_temp (id, nomor_kamar, lantai, kapasitas, harga_per_bulan, status, fasilitas, catatan, created_at, updated_at)
                SELECT id, nomor_kamar, lantai, kapasitas, harga_per_bulan, status, fasilitas, catatan, created_at, updated_at FROM asrama_kamars;");

            DB::statement("DROP TABLE asrama_kamars;");
            DB::statement("ALTER TABLE asrama_kamars_temp RENAME TO asrama_kamars;");
        } elseif (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE asrama_kamars MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'Tersedia'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
