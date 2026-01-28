<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Ketua Kelompok (Yang Bayar)
        $table->foreignId('tryout_id')->constrained()->cascadeOnDelete();
        
        $table->string('invoice_code')->unique();
        $table->decimal('amount', 12, 2); // Total Harga setelah diskon
        $table->decimal('unit_price', 12, 2);
        $table->integer('qty'); // Jumlah Peserta
        
        $table->json('participants_data')->nullable(); // Simpan email anggota di sini
        
        $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
        $table->string('snap_token')->nullable(); // Untuk Midtrans nanti
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
