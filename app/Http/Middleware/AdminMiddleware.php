<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->is_admin) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Forbidden. Admin access required.'], 403);
            }

            return redirect()->route('login')->with('error', 'Admin access required.');
        }

        return $next($request);
    }
}
