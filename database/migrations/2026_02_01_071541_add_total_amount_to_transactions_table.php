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
    Schema::table('transactions', function (Blueprint $table) {
        // Adding total_amount, likely a decimal. Adjust precision if needed.
        // Placing it after 'amount' based on your query structure.
        $table->decimal('total_amount', 15, 2)->default(0)->after('amount');
    });
}

public function down()
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->dropColumn('total_amount');
    });
}
};
