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
    // Tambah kolom info bank di users
    Schema::table('users', function (Blueprint $table) {
        $table->json('bank_info')->nullable()->after('affiliate_balance');
    });

    // Tabel untuk melacak permintaan pencairan dana
    Schema::create('withdrawal_requests', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->decimal('amount', 12, 2);
        $table->json('bank_details'); // Salinan info bank saat pengajuan
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
        $table->text('admin_note')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('affiliate_extensions');
    }
};
