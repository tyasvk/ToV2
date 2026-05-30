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
        Schema::table('users', function (Blueprint $table) {
            // Ubah tipe kolom referred_by menjadi string (varchar)
            // agar bisa menyimpan kode afiliasi seperti 'T0CERF3'
            $table->string('referred_by')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Kembalikan ke unsignedBigInteger jika migrasi di-rollback
            $table->unsignedBigInteger('referred_by')->nullable()->change();
        });
    }
};