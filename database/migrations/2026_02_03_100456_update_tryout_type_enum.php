<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Menggunakan DB statement karena Blueprint->change() untuk enum 
        // memerlukan dependensi doctrine/dbal yang terkadang bermasalah pada versi terbaru
        DB::statement("ALTER TABLE tryouts MODIFY COLUMN type ENUM('general', 'akbar', 'adidaya') DEFAULT 'general'");
    }

    public function down()
    {
        // Kembalikan ke asal jika diperlukan (Pastikan tidak ada data 'adidaya' saat rollback)
        DB::statement("ALTER TABLE tryouts MODIFY COLUMN type ENUM('general', 'akbar') DEFAULT 'general'");
    }
};