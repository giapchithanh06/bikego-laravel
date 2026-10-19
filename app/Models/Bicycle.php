<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/** A bicycle. bike_code is the licence plate shown to users. */
class Bicycle extends Model
{
    use SoftDeletes;

    public const LOCATION_AT_STATION = 'at_station';
    public const LOCATION_RENTED = 'rented';

    public const CONDITION_NORMAL = 'normal';
    public const CONDITION_REPAIRING = 'repairing';

    protected $fillable = [
        'bike_code',
        'station_id',
        'price_per_minute',
        'location_status',
        'condition_status',
    ];

    public static function conditionLabels(): array
    {
        return [
            self::CONDITION_NORMAL => 'Bình thường',
            self::CONDITION_REPAIRING => 'Đang sửa',
        ];
    }

    /** The station keeps being visible even if it was moved to the trash. */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class)->withTrashed();
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    public function activeRental(): HasOne
    {
        return $this->hasOne(Rental::class)->whereNull('ended_at');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BicycleReview::class);
    }

    public function isRented(): bool
    {
        return $this->location_status === self::LOCATION_RENTED;
    }

    public function conditionLabel(): string
    {
        return self::conditionLabels()[$this->condition_status] ?? $this->condition_status;
    }
}
