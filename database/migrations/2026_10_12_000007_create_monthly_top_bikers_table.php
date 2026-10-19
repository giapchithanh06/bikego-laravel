<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pre-computed "Top Bikers" ranking, one row per (month, rank).
     * It is filled automatically at 00:00 on the first day of every month (see routes/console.php)
     * so the home page only reads this table and never has to aggregate rentals again.
     */
    public function up(): void
    {
        Schema::create('monthly_top_bikers', function (Blueprint $table) {
            $table->id();
            $table->char('month', 7); // format YYYY-MM
            $table->unsignedTinyInteger('rank');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('total_minutes');
            $table->unsignedInteger('total_rentals');
            $table->timestamps();

            $table->unique(['month', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monthly_top_bikers');
    }
};
