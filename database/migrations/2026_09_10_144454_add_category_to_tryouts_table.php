<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tryouts', function (Blueprint $table) {
            // Menambahkan kolom category (default: skd)
            $table->enum('category', ['skd', 'skb'])->default('skd')->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('tryouts', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};