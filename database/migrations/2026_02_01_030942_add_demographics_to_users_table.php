<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambah kolom demografi
            $table->string('instance_type', 1)->nullable()->after('email'); // 1: Pusat, 2: Daerah
            $table->string('province_code', 2)->nullable()->after('instance_type');
            $table->string('gender', 1)->nullable()->after('province_code'); // 1: Laki, 2: Perempuan
            
            // Pastikan kolom participant_number ada (jika belum)
            if (!Schema::hasColumn('users', 'participant_number')) {
                $table->string('participant_number', 20)->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['instance_type', 'province_code', 'gender', 'participant_number']);
        });
    }
};