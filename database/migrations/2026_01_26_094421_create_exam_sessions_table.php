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
    Schema::create('exam_sessions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('tryout_id')->constrained()->onDelete('cascade');
        
        // TAMBAHKAN KOLOM INI:
        // Kita gunakan string dengan default 'ongoing' (sedang dikerjakan)
        $table->string('status')->default('ongoing'); 
        
        $table->json('answers')->nullable();
        $table->integer('score_twk')->default(0);
        $table->integer('score_tiu')->default(0);
        $table->integer('score_tkp')->default(0);
        $table->integer('score_total')->default(0);
        $table->timestamp('started_at');
        $table->timestamp('ended_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};
