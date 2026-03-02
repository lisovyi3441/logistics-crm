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
            $table->string('pickup_address')->nullable()->after('notes');
            $table->decimal('pickup_lat', 10, 8)->nullable()->after('pickup_address');
            $table->decimal('pickup_lng', 11, 8)->nullable()->after('pickup_lat');

            $table->string('delivery_address')->nullable()->after('pickup_lng');
            $table->decimal('delivery_lat', 10, 8)->nullable()->after('delivery_address');
            $table->decimal('delivery_lng', 11, 8)->nullable()->after('delivery_lat');

            $table->decimal('distance_km', 10, 2)->nullable()->after('delivery_lng');
            $table->integer('transit_time_minutes')->nullable()->after('distance_km');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_address',
                'pickup_lat',
                'pickup_lng',
                'delivery_address',
                'delivery_lat',
                'delivery_lng',
                'distance_km',
                'transit_time_minutes',
            ]);
        });
    }
};
