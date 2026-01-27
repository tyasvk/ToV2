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
    Schema::create('questions', function (Blueprint $table) {
        $table->id();
        
        // TAMBAHKAN BARIS INI:
        // Menghubungkan soal ke tabel tryouts dan otomatis hapus soal jika tryout dihapus
        $table->foreignId('tryout_id')->constrained()->onDelete('cascade');
        
        $table->enum('type', ['TWK', 'TIU', 'TKP']);
        $table->text('content');
        $table->json('options');
        $table->string('correct_answer')->nullable();
        $table->json('tkp_scores')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
