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
        Schema::table('order_items', function (Blueprint $table) {
            $table->integer('length_cm')->nullable();
            $table->integer('width_cm')->nullable();
            $table->integer('height_cm')->nullable();
            $table->decimal('cbm', 8, 3)->nullable();
            $table->boolean('is_dangerous')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['length_cm', 'width_cm', 'height_cm', 'cbm', 'is_dangerous']);
        });
    }
};
