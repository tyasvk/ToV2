<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Ubah kolom lama menjadi nullable agar tidak wajib diisi
            $table->foreignId('tryout_id')->nullable()->change();
            $table->decimal('unit_price', 12, 2)->nullable()->change();
            $table->integer('qty')->nullable()->change();

            // Tambahkan kolom baru jika belum ada
            if (!Schema::hasColumn('transactions', 'description')) {
                $table->string('description')->after('amount')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'metadata')) {
                $table->json('metadata')->after('description')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('tryout_id')->nullable(false)->change();
            $table->decimal('unit_price', 12, 2)->nullable(false)->change();
            $table->integer('qty')->nullable(false)->change();
            $table->dropColumn(['description', 'metadata']);
        });
    }
};