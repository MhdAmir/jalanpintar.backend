<?php

namespace App\Services;

use App\Models\RefreshToken;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RefreshTokenStore
{
    /**
     * Create (mint) a new refresh token for a user
     *
     * @param string $userId UUID of the user
     * @param int $minutes
     * @param Request|null $request
     * @return string Raw token (unhashed) to return to client
     */
    public function mint(string $userId, int $minutes, ?Request $request = null): string
    {
        $rawToken = bin2hex(random_bytes(64));
        $hashedToken = hash('sha256', $rawToken);

        // Explicit type checking and conversion
        if (!is_int($minutes)) {
            \Log::warning('RefreshTokenStore::mint received non-integer minutes', [
                'minutes' => $minutes,
                'type' => gettype($minutes)
            ]);
            $minutes = (int) $minutes;
        }

        RefreshToken::create([
            'user_id' => $userId,
            'token' => $hashedToken,
            'expires_at' => \Carbon\Carbon::now()->addMinutes($minutes),
        ]);

        return $rawToken;
    }

    /**
     * Validate a refresh token and return the user ID if valid
     *
     * @param string $rawToken
     * @return string|null User UUID if valid, null otherwise
     */
    public function validate(string $rawToken): ?string
    {
        $hashedToken = hash('sha256', $rawToken);

        $refreshToken = RefreshToken::where('token', $hashedToken)
            ->where('revoked', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        return $refreshToken ? $refreshToken->user_id : null;
    }

    /**
     * Revoke a refresh token
     *
     * @param string $rawToken
     * @return bool
     */
    public function revoke(string $rawToken): bool
    {
        $hashedToken = hash('sha256', $rawToken);

        return RefreshToken::where('token', $hashedToken)
            ->update(['revoked' => true]) > 0;
    }

    /**
     * Revoke all refresh tokens for a user
     *
     * @param string $userId User UUID
     * @return int Number of tokens revoked
     */
    public function revokeAllForUser(string $userId): int
    {
        return RefreshToken::where('user_id', $userId)
            ->update(['revoked' => true]);
    }
}
