<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A ward (phuong / xa) of Ho Chi Minh City. */
class Ward extends Model
{
    protected $fillable = ['code', 'name'];

    public function stations(): HasMany
    {
        return $this->hasMany(Station::class);
    }
}
