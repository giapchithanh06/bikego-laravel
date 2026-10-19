<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A bike station.
 *
 * Handy computed attributes (available after scopeWithStats()):
 *  - bikes_present : bikes currently parked in the station (they occupy docks)
 *  - bikes_rentable: bikes that can be rented right now (parked + normal condition)
 *  - reviews_avg_rating / reviews_count
 */
class Station extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_MAINTENANCE = 'maintenance';

    protected $fillable = ['ward_id', 'name', 'address', 'total_docks', 'status'];

    public static function statusLabels(): array
    {
        return [
            self::STATUS_ACTIVE => 'Đang hoạt động',
            self::STATUS_MAINTENANCE => 'Đang bảo trì',
        ];
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function bicycles(): HasMany
    {
        return $this->hasMany(Bicycle::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(StationReview::class);
    }

    /** Add bike counters and the average rating to a query. */
    public function scopeWithStats(Builder $query): Builder
    {
        return $query
            ->withCount([
                'bicycles as bikes_present' => fn ($q) => $q->where('location_status', Bicycle::LOCATION_AT_STATION),
                'bicycles as bikes_rentable' => fn ($q) => $q
                    ->where('location_status', Bicycle::LOCATION_AT_STATION)
                    ->where('condition_status', Bicycle::CONDITION_NORMAL),
            ])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    /** Free docks = total docks - bikes parked here (never below zero). */
    public function emptyDocks(): int
    {
        $present = $this->bikes_present ?? $this->bicycles()
            ->where('location_status', Bicycle::LOCATION_AT_STATION)->count();

        return max(0, $this->total_docks - $present);
    }
}
