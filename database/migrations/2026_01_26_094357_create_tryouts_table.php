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
    Schema::create('tryouts', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->integer('duration_minutes')->default(110); // Default 110 menit
        $table->boolean('is_active')->default(false); // false = Draft, true = Publish
        
        // Kolom Tanggal Baru
        $table->timestamp('published_at')->nullable(); // Kapan paket tampil di katalog
        $table->timestamp('started_at')->nullable();   // Kapan tombol "Mulai" aktif
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tryouts');
    }
};
