<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Symfony\Component\HttpFoundation\Response;

class JwtFromCookie
{
    public function handle(Request $request, Closure $next, $role = null): Response
    {
        try {
            // Cookie name & guard based on role
            if ($role === 'admin') {
                $cookieName = 'admin_token';
                $guard = 'admin';
            } else {
                $cookieName = 'token';
                $guard = 'api';
            }

            // Get token from cookie
            $token = $request->cookie($cookieName);
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token not found'
                ], 401);
            }

            // Set the correct guard before authenticating
            auth()->shouldUse($guard);

            // Authenticate using token
            $user = JWTAuth::setToken($token)->authenticate();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => ucfirst($role ?? 'user') . ' not found'
                ], 404);
            }

            // Extra role check for admin
            if ($role === 'admin' && (!$role || $role !== 'admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied: Admins only'
                ], 403);
            }

            // Attach authenticated model to request
            $request->merge(['auth_user' => $user]);

            return $next($request);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: ' . $e->getMessage()
            ], 401);
        }
    }
}
