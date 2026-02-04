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
        // Menambahkan kolom ke tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->string('affiliate_code')->unique()->nullable()->after('password');
            $table->decimal('affiliate_balance', 12, 2)->default(0)->after('affiliate_code');
        });

        // Menambahkan kolom ke tabel transactions
        Schema::table('transactions', function (Blueprint $table) {
            // ID pemilik kode voucher (referrer)
            $table->foreignId('referrer_id')->nullable()->after('tryout_id')->constrained('users')->nullOnDelete();
            
            // Jumlah komisi yang didapat pemilik kode (3000)
            $table->decimal('affiliate_commission', 12, 2)->default(0)->after('unit_price');
            
            // Menambahkan kolom discount_amount untuk mencatat potongan 3000 bagi pembeli
            $table->decimal('discount_amount', 12, 2)->default(0)->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['affiliate_code', 'affiliate_balance']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['referrer_id']);
            $table->dropColumn(['referrer_id', 'affiliate_commission', 'discount_amount']);
        });
    }
};