<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Pastikan kolom bank_info ada di tabel users
        if (!Schema::hasColumn('users', 'bank_info')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('bank_info')->nullable()->after('affiliate_balance');
            });
        }

        // 2. Buat tabel withdrawal_requests
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->json('bank_details'); // Menyimpan snapshot data bank saat pengajuan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_requests');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bank_info');
        });
    }
};