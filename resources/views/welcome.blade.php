    <!-- // public function login(Request $request)
    // {
    //     $credentials = $request->only('username', 'password');

    // If authentication fails, return error
    // if (!auth('admin')->attempt($credentials)) {
    //     return response()->json(['message' => 'Invalid credentials'], 401);
    // }

    // Authenticated user instance
    // $admin = auth('admin')->user();

    // Generate JWT token from authenticated user
    //     $jwtToken = JWTAuth::fromUser($admin);

    //     session(['admin_id' => $admin->id]);

    //     $response = new Response([
    //         'admin' => $admin,
    //         'message' => 'Login successful!',
    //     ]);

    //     $cookie = Cookie::make(
    //         'admin_token',
    //         $jwtToken,
    //         60 * 24,
    //         null,
    //         null,
    //         false,
    //         true
    //     );

    //     return $response->withCookie($cookie);
    // }

    // public function logout()
    // {
    //     Auth::guard('api')->logout();
    //     return response()->json(['message' => 'Logged out'])
    //         ->cookie('admin_token', '', -1, '/', null, true, true, false, 'Strict');
    // }

    // public function getAdmin(Request $request)
    // {
    //     $token = $request->cookie('admin_token');

    //     if (!$token) {
    //         return response()->json(['message' => 'Token not found'], 401);
    //     }

    //     try {
    //         $admin = JWTAuth::setToken($token)->authenticate();

    //         if (!$admin) {
    //             return response()->json(['message' => 'Admin not found'], 404);
    //         }

    //         return response()->json([
    //             'message' => 'Admin fetched successfully',
    //             'admin'   => $admin
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Invalid token',
    //             'error'   => $e->getMessage()
    //         ], 401);
    //     }
    // } -->