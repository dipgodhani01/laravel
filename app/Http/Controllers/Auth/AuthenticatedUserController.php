<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthenticatedUserController extends Controller
{
    public function getUser(Request $request)
    {
        try {
            $token = $request->cookie('token');
            if (!$token) {
                return response()->json(['message' => 'Token not found'], 401);
            }

            $user = JWTAuth::setToken($token)->authenticate();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            return response()->json([
                'user' => $user
            ], 200);
        } catch (JWTException $e) {
            return response()->json([
                'message' => 'Token invalid or expired',
                'error' => $e->getMessage()
            ], 401);
        }
    }
}