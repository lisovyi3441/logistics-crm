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
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->integer('price_per_km_cents')->default(50); // Equivalent to 50 cents per kg/km
            $table->decimal('insurance_rate_percent', 5, 2)->default(1.00);
            $table->decimal('tax_rate_percent', 5, 2)->default(20.00);
            $table->decimal('adr_surcharge_percent', 5, 2)->default(25.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tariffs');
    }
};
