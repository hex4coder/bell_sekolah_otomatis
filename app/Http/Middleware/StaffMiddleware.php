<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isAdmin() && ! $request->user()->isStaff()) {
            abort(403, 'Akses hanya untuk admin atau staf.');
        }

        return $next($request);
    }
}
