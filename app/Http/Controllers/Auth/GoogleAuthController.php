<?php


namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Response;

class GoogleAuthController extends Controller
{
    public function handleGoogleCallback(Request $request)
    {
        try {
            $code = $request->get('code');
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => config('services.google.redirect'),
                'grant_type' => 'authorization_code',
            ]);

            $tokenData = $response->json();

            if (!isset($tokenData['access_token'])) {
                return response()->json(['message' => 'Access token not received', 'error' => $tokenData], 400);
            }

            $googleUser = Http::withHeaders([
                'Authorization' => 'Bearer ' . $tokenData['access_token'],
            ])->get('https://www.googleapis.com/oauth2/v2/userinfo')->json();

            $user = User::updateOrCreate(
                ['user_id' => $googleUser['id']],
                [
                    'name' => $googleUser['name'],
                    'email' => $googleUser['email'],
                    'image' => $googleUser['picture'],
                    'email_verified_at' => now(),
                ]
            );

            $jwtToken = JWTAuth::fromUser($user);

            $response = new Response([
                'user' => $user,
                'message' => 'Login successful!',
            ]);

            $cookie = cookie(
                'token',        // Cookie name
                $jwtToken,      // Cookie value
                60 * 24,        // Expiry in minutes (24 hours)
                null,           // Path
                null,           // Domain (null = current domain)
                false,          // Secure (set true if using HTTPS)
                true            // HttpOnly (JS cannot access)
            );

            return $response->withCookie($cookie);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Google login failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        $cookie = cookie()->forget('token');
        return response()->json(['message' => 'Logged out'])->withCookie($cookie);
    }
}
