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
            // 1. Tambahkan kolom ended_at jika belum ada
            if (!Schema::hasColumn('tryouts', 'ended_at')) {
                $table->timestamp('ended_at')->nullable()->after('started_at');
            }

            // 2. Ubah nama kolom duration_minutes menjadi duration (agar sesuai dengan Controller)
            if (Schema::hasColumn('tryouts', 'duration_minutes') && !Schema::hasColumn('tryouts', 'duration')) {
                $table->renameColumn('duration_minutes', 'duration');
            } 
            // Jika duration_minutes tidak ada tapi duration juga tidak ada (kasus jarang), buat baru
            elseif (!Schema::hasColumn('tryouts', 'duration')) {
                $table->integer('duration')->default(120)->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tryouts', function (Blueprint $table) {
            // Rollback ended_at
            if (Schema::hasColumn('tryouts', 'ended_at')) {
                $table->dropColumn('ended_at');
            }

            // Rollback rename duration
            if (Schema::hasColumn('tryouts', 'duration')) {
                $table->renameColumn('duration', 'duration_minutes');
            }
        });
    }
};