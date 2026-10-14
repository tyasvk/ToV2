<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tryouts', function (Blueprint $table) {
            $table->integer('passing_grade')->nullable()->after('category')->comment('Hanya digunakan untuk tryout kategori SKB');
        });
    }

    public function down(): void
    {
        Schema::table('tryouts', function (Blueprint $table) {
            $table->dropColumn('passing_grade');
        });
    }
};