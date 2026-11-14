<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skb_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 20)->default('💼');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('tryouts', function (Blueprint $table) {
            $table->foreignId('skb_category_id')
                  ->nullable()
                  ->after('category')
                  ->constrained('skb_categories')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tryouts', function (Blueprint $table) {
            $table->dropForeign(['skb_category_id']);
            $table->dropColumn('skb_category_id');
        });

        Schema::dropIfExists('skb_categories');
    }
};