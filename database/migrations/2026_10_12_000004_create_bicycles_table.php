<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Bicycles.
     * - station_id is NULL while the bike is rented.
     * - location_status: at_station | rented
     * - condition_status: normal | repairing
     */
    public function up(): void
    {
        Schema::create('bicycles', function (Blueprint $table) {
            $table->id();
            $table->string('bike_code', 20)->unique();
            $table->foreignId('station_id')->nullable()->constrained('stations');
            $table->unsignedInteger('price_per_minute')->default(500);
            $table->string('location_status', 20)->default('at_station');
            $table->string('condition_status', 20)->default('normal');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['station_id', 'location_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bicycles');
    }
};
