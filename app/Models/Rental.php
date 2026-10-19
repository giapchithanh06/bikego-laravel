<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** One rental of a bicycle by a user. A rental without ended_at is still in progress. */
class Rental extends Model
{
    protected $fillable = [
        'user_id',
        'bicycle_id',
        'bike_code',
        'start_station_id',
        'start_station_name',
        'end_station_id',
        'end_station_name',
        'started_at',
        'ended_at',
        'price_per_minute',
        'total_minutes',
        'total_amount',
    ];

    protected $casts = [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bicycle(): BelongsTo
    {
        return $this->belongsTo(Bicycle::class)->withTrashed();
    }

    public function startStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'start_station_id')->withTrashed();
    }

    public function endStation(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'end_station_id')->withTrashed();
    }

    public function isActive(): bool
    {
        return $this->ended_at === null;
    }

    /**
     * Rented minutes between two moments, rounded UP to a whole minute (minimum 1 minute).
     */
    public static function minutesBetween(Carbon $start, Carbon $end): int
    {
        $seconds = max(0, $end->getTimestamp() - $start->getTimestamp());

        return max(1, (int) ceil($seconds / 60));
    }

    /** Minutes rented so far (for a running rental) or the final value (for a finished one). */
    public function elapsedMinutes(): int
    {
        if ($this->total_minutes !== null) {
            return (int) $this->total_minutes;
        }

        return self::minutesBetween($this->started_at, now());
    }

    /** Amount = unit price x number of minutes. */
    public function currentAmount(): int
    {
        if ($this->total_amount !== null) {
            return (int) $this->total_amount;
        }

        return $this->elapsedMinutes() * (int) $this->price_per_minute;
    }
}
