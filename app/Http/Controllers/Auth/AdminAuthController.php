<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminAuthController extends Controller
{
    private function getAdminFromToken($token)
    {
        $payload = JWTAuth::setToken($token)->getPayload();
        $adminId = $payload->get('admin_id');
        return Admin::find($adminId);
    }
    public function createAdmin(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $admin = new Admin();
            $admin->username = $request->username;
            $admin->password = Hash::make($request->password);
            $admin->save();

            return response()->json([
                'success' => true,
                'message' => 'Admin created successfully.',
                'admin' => $admin,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function loginAdmin(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ]);

            $admin = Admin::where('username', $request->username)->first();

            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin not found',
                ], 404);
            }

            if (!Hash::check($request->password, $admin->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password',
                ], 401);
            }


            $token = JWTAuth::claims([
                'role' => 'admin',
                'admin_id' => $admin->_id
            ])->fromUser($admin);

            $admin->login_at = Carbon::now();
            $admin->save();

            $response = response()->json([
                'success' => true,
                'message' => 'Login successful',
                'admin' => $admin,
            ]);

            $cookie = cookie(
                'admin_token', // Cookie name
                $token,        // Cookie value
                60 * 24,       // Expiry in minutes (24 hours)
                null,          // Path
                null,          // Domain
                false,         // Secure (set true if HTTPS)
                true           // HttpOnly
            );

            return $response->withCookie($cookie);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function getAdmin(Request $request)
    {
        try {
            $token = $request->cookie('admin_token');
            if (!$token) {
                return response()->json(['success' => false, 'message' => 'Token not found'], 401);
            }

            $admin = $this->getAdminFromToken($token);
            if (!$admin) {
                return response()->json(['success' => false, 'message' => 'Admin not found'], 404);
            }

            return response()->json(['success' => true, 'admin' => $admin]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token problem: ' . $e->getMessage()
            ], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->cookie('admin_token');
            if (!$token) {
                return response()->json(['message' => 'Token not found'], 401);
            }
            auth()->shouldUse('admin');

            JWTAuth::setToken($token)->invalidate();

            $cookie = cookie()->forget('admin_token');

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ])->withCookie($cookie);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout, please try again',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function getAllUsers(Request $request)
    {
        try {
            $token = $request->cookie('admin_token');
            if (!$token) {
                return response()->json(['message' => 'Token not found'], 401);
            }

            $users = User::all();

            return response()->json([
                'success' => true,
                'users' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
