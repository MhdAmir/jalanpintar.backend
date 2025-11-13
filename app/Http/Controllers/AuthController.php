<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RefreshToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\MeResource;
use App\Helpers\Cookies;
use App\Services\RefreshTokenStore;

class AuthController extends Controller
{
    protected $store;

    public function __construct(RefreshTokenStore $store)
    {
        $this->store = $store;
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $accessToken = JWTAuth::fromUser($user);

        // Use hardcoded value to ensure integer type
        $rtMinutes = 20160; // 14 days
        $rawRT = $this->store->mint($user->id, $rtMinutes, $request);

        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => new MeResource($user),
        ])->withCookie(Cookies::refreshToken($rawRT, $rtMinutes));
    }


    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        $accessToken = JWTAuth::attempt($credentials);

        if (!$accessToken) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = JWTAuth::user();

        if (!$user) {
            $user = User::where('email', $credentials['email'])->first();
        }

        // Use hardcoded value to ensure integer type
        $rtMinutes = 20160; // 14 days
        $rawRT = $this->store->mint($user->id, $rtMinutes, $request);

        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => new MeResource($user),
        ])->withCookie(Cookies::refreshToken($rawRT, $rtMinutes));
    }


    public function refresh(Request $request)
    {
        $rawToken = $request->cookie('refresh_token');

        if (!$rawToken) {
            return response()->json(['message' => 'Refresh token not provided'], 401);
        }

        $userId = $this->store->validate($rawToken);

        if (!$userId) {
            return response()->json(['message' => 'Invalid or expired refresh token'], 401);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Revoke old token and create new one (token rotation)
        $this->store->revoke($rawToken);

        $accessToken = JWTAuth::fromUser($user);

        // Use hardcoded value to ensure integer type
        $rtMinutes = 20160; // 14 days
        $newRawRT = $this->store->mint($user->id, $rtMinutes, $request);

        return response()->json([
            'access_token' => $accessToken,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => new MeResource($user),
        ])->withCookie(Cookies::refreshToken($newRawRT, $rtMinutes));
    }


    public function logout(Request $request)
    {
        // Invalidate current JWT
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
        } catch (JWTException $e) {
            // Continue, we'll still try to revoke refresh token
        }

        // Revoke refresh token from cookie
        $rawToken = $request->cookie('refresh_token');
        if ($rawToken) {
            $this->store->revoke($rawToken);
        }

        return response()->json(['message' => 'Successfully logged out'])
            ->withCookie(Cookies::forgetRefreshToken());
    }


    public function getUser()
    {
        $user = JWTAuth::user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json(new MeResource($user));
    }

    public function updateUser(Request $request)
    {
        $user = JWTAuth::user();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->update($request->only(['name', 'email']));

        return response()->json(new MeResource($user));
    }
}