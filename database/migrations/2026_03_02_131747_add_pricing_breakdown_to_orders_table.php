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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('base_price_cents')->default(0)->after('status');
            $table->unsignedBigInteger('insurance_fee_cents')->default(0)->after('base_price_cents');
            $table->unsignedBigInteger('surcharge_cents')->default(0)->after('insurance_fee_cents');
            $table->unsignedBigInteger('discount_cents')->default(0)->after('surcharge_cents');
            $table->unsignedBigInteger('tax_cents')->default(0)->after('discount_cents');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'base_price_cents',
                'insurance_fee_cents',
                'surcharge_cents',
                'discount_cents',
                'tax_cents',
            ]);
        });
    }
};
