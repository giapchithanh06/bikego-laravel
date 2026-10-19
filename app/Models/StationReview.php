<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A user's rating (1-5) and comment about a station. */
class StationReview extends Model
{
    protected $fillable = ['rental_id', 'user_id', 'station_id', 'rating', 'comment'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class)->withTrashed();
    }
}
