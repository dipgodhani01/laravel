<?php

namespace App\Http\Middleware;

use Closure;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        if ($request->cookie('admin_token')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->cookie('admin_token'));
        }

        return $next($request);
    }
}
