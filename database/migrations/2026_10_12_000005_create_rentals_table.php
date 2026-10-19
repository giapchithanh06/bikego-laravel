<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rental history.
     * bike_code and the station names are copied ("snapshot") so the history stays readable
     * even if a bike or a station is permanently deleted later.
     */
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bicycle_id')->nullable()->constrained('bicycles')->nullOnDelete();
            $table->string('bike_code', 20);
            $table->foreignId('start_station_id')->nullable()->constrained('stations')->nullOnDelete();
            $table->string('start_station_name');
            $table->foreignId('end_station_id')->nullable()->constrained('stations')->nullOnDelete();
            $table->string('end_station_name')->nullable();
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->unsignedInteger('price_per_minute');
            $table->unsignedInteger('total_minutes')->nullable();
            $table->unsignedBigInteger('total_amount')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'ended_at']);
            $table->index('ended_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
