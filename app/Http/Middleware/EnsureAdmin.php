<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only logged-in users whose role is "admin" may open /admin/* pages.
 * Everybody else is redirected to the admin login page (/admin/login).
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== User::ROLE_ADMIN) {
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
