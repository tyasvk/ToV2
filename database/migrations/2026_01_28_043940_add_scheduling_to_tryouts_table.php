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
    Schema::table('tryouts', function (Blueprint $table) {
        // Tambahkan is_published jika belum ada
        if (!Schema::hasColumn('tryouts', 'is_published')) {
            $table->boolean('is_published')->default(false)->after('is_paid');
        }

        // published_at dilewati jika sudah ada, atau tambahkan jika belum
        if (!Schema::hasColumn('tryouts', 'published_at')) {
            $table->datetime('published_at')->nullable()->after('is_published');
        }

        // Tambahkan started_at jika belum ada
        if (!Schema::hasColumn('tryouts', 'started_at')) {
            $table->datetime('started_at')->nullable()->after('published_at');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tryouts', function (Blueprint $table) {
            //
        });
    }
};
