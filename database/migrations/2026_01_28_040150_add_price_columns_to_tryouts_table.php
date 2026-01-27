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
        // Cek jika kolom 'price' belum ada
        if (!Schema::hasColumn('tryouts', 'price')) {
            $table->decimal('price', 15, 2)->default(0)->after('description');
        }
        
        // Cek jika kolom 'is_paid' belum ada
        if (!Schema::hasColumn('tryouts', 'is_paid')) {
            $table->boolean('is_paid')->default(false)->after('price');
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
