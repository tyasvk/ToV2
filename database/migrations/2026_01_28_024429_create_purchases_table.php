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
    Schema::create('purchases', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('tryout_id')->constrained()->onDelete('cascade');
        $table->decimal('amount', 15, 2); // Untuk menyimpan harga saat dibeli
        $table->string('payment_method')->nullable(); // Contoh: gopay, bank_transfer
        $table->string('status')->default('pending'); // pending, success, failed
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
