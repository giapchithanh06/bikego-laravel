<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Station reviews and bicycle reviews (rating 1-5, comment up to 200 characters). */
    public function up(): void
    {
        Schema::create('station_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->nullable()->unique()->constrained('rentals')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('station_id')->constrained('stations')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('comment', 200)->nullable();
            $table->timestamps();

            $table->index('station_id');
        });

        Schema::create('bicycle_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->nullable()->unique()->constrained('rentals')->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bicycle_id')->constrained('bicycles')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('comment', 200)->nullable();
            $table->timestamps();

            $table->index('bicycle_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bicycle_reviews');
        Schema::dropIfExists('station_reviews');
    }
};
