<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('tryouts', function (Blueprint $table) {
        // Menambahkan kolom tanggal selesai pendaftaran setelah kolom started_at
        $table->dateTime('end_date')->nullable()->after('started_at');
    });
}

public function down()
{
    Schema::table('tryouts', function (Blueprint $table) {
        $table->dropColumn('end_date');
    });
}
};
