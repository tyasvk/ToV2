<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tryouts', function (Blueprint $table) {
            $table->dateTime('registration_start_at')->nullable()->after('description');
            $table->dateTime('registration_end_at')->nullable()->after('registration_start_at');
        });
    }

    public function down(): void
    {
        Schema::table('tryouts', function (Blueprint $table) {
            $table->dropColumn(['registration_start_at', 'registration_end_at']);
        });
    }
};