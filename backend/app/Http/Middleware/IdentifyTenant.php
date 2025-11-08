<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        // Get tenant from subdomain or X-School-Id header
        $schoolId = $request->header('X-School-Id') 
            ?? $request->get('school_id')
            ?? $request->user()?->school_id;

        if ($schoolId) {
            // Set tenant context
            tenancy()->initialize(Tenant::find($schoolId));
        }

        return $next($request);
    }
}

