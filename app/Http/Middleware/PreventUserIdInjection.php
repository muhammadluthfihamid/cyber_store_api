<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventUserIdInjection
{
    /**
     * Handle an incoming request.
     * Prevents clients from injecting or overriding user_id, customer_id, or role in request payloads.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is authenticated as customer (or non-admin request), prevent payload user_id/role injection
        if ($user && !$user->isAdmin()) {
            if ($request->has('user_id')) {
                $request->request->remove('user_id');
                $request->query->remove('user_id');
            }

            if ($request->has('customer_id')) {
                $request->request->remove('customer_id');
                $request->query->remove('customer_id');
            }

            if ($request->has('role')) {
                $request->request->remove('role');
                $request->query->remove('role');
            }

            if ($request->has('is_active')) {
                $request->request->remove('is_active');
                $request->query->remove('is_active');
            }
        }

        return $next($request);
    }
}
