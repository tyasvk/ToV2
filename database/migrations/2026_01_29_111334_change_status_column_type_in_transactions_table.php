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
        Schema::table('transactions', function (Blueprint $table) {
            // Kita ubah dari ENUM ke STRING agar lebih fleksibel
            // dan tidak error saat kita memasukkan kata 'success' atau 'paid'
            $table->string('status', 20)->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Kembalikan ke enum jika diperlukan (sesuaikan dengan enum awal Anda)
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending')->change();
        });
    }
};