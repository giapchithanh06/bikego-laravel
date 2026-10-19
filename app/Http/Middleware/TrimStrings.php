<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The full name must be validated exactly as typed (no silent trimming).
     *
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
        'name',
    ];
}
