<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Stored result of the monthly "Top Bikers" ranking (see bikego:summarize-monthly). */
class MonthlyTopBiker extends Model
{
    protected $fillable = ['month', 'rank', 'user_id', 'total_minutes', 'total_rentals'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** "42 giờ 10 phút" style text. */
    public function durationText(): string
    {
        return intdiv($this->total_minutes, 60).' giờ '.str_pad((string) ($this->total_minutes % 60), 2, '0', STR_PAD_LEFT).' phút';
    }
}
