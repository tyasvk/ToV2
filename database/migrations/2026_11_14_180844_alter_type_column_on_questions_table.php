<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah tipe kolom 'type' dari ENUM menjadi VARCHAR(50) agar menerima 'SKB', 'TWK', 'TIU', 'TKP', dll.
        DB::statement("ALTER TABLE questions MODIFY COLUMN type VARCHAR(50) NOT NULL DEFAULT 'TWK'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE questions MODIFY COLUMN type ENUM('TWK', 'TIU', 'TKP') NOT NULL");
    }
};
