<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    // 1. Tambah kolom saldo ke user
    Schema::table('users', function (Blueprint $table) {
        $table->decimal('balance', 15, 2)->default(0)->after('email');
    });

    // 2. Buat tabel riwayat transaksi dompet
    Schema::create('wallet_transactions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->string('type'); // 'credit' (masuk/topup) atau 'debit' (keluar/bayar)
        $table->decimal('amount', 15, 2);
        $table->string('description');
        $table->string('status')->default('pending'); // pending, success, failed
        $table->string('proof_payment')->nullable(); // Foto bukti transfer (jika manual)
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('wallet_transactions');
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('balance');
    });
}
};
