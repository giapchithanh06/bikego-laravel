<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Bike stations. status: active | maintenance. Supports soft delete (trash). */
    public function up(): void
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ward_id')->constrained('wards');
            $table->string('name');
            $table->string('address');
            $table->unsignedInteger('total_docks');
            $table->string('status', 20)->default('active');
            $table->softDeletes();
            $table->timestamps();

            $table->index(['ward_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stations');
    }
};
