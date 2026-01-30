<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
{
    Schema::table('tryouts', function (Blueprint $table) {
        $table->enum('type', ['general', 'akbar'])->default('general')->after('id');
        $table->text('requirements')->nullable()->after('description'); // Untuk syarat (HTML/Text)
    });
}

public function down()
{
    Schema::table('tryouts', function (Blueprint $table) {
        $table->dropColumn(['type', 'requirements']);
    });
}
};
