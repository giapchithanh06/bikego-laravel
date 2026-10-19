<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

/**
 * Application user. role = "user" (default) or "admin".
 */
class User extends Authenticatable
{
    use Notifiable;

    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
            'email_verified_at' => 'datetime',
        ];

    /** Hash the password automatically (Laravel 8 has no "hashed" cast). */
    public function setPasswordAttribute($value): void
    {
        $this->attributes['password'] = Hash::info($value)['algoName'] === 'unknown' ? Hash::make($value) : $value;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function rentals(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /** The rental that has not been returned yet (a user can have at most one). */
    public function activeRental(): ?Rental
    {
        return $this->rentals()->whereNull('ended_at')->latest('id')->first();
    }

    /**
     * Convert 0xxxxxxxxx / 84xxxxxxxxx / +84xxxxxxxxx to the canonical 0xxxxxxxxx format.
     */
    public static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);

        if (preg_match('/^\+?84(\d{9})$/', $phone, $m)) {
            return '0'.$m[1];
        }

        return $phone;
    }

    /** Phone shown on the ranking: only the last 3 digits are visible. */
    public function maskedPhone(): string
    {
        return $this->phone ? '***'.substr($this->phone, -3) : '';
    }
}
