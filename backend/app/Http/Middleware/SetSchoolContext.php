<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSchoolContext
{
    public function handle(Request $request, Closure $next): Response
    {
        // Set school context from authenticated user
        if ($request->user() && $request->user()->school_id) {
            // Add school_id to request for easy access
            $request->merge(['school_id' => $request->user()->school_id]);
        }

        return $next($request);
    }
}

