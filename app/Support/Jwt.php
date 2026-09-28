<?php

namespace App\Support;

/**
 * Minimal HS256 JWT implementation — wire-compatible with the Next.js
 * jsonwebtoken / jose tokens (same secret, same algorithm).
 */
class Jwt
{
    protected static function secret(): string
    {
        return env('JWT_SECRET', 'trinetra_secret');
    }

    protected static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected static function b64decode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * @param  array  $payload
     * @param  int  $ttlSeconds  token lifetime in seconds
     */
    public static function sign(array $payload, int $ttlSeconds): string
    {
        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $now = time();
        $payload['iat'] = $now;
        $payload['exp'] = $now + $ttlSeconds;

        $segments = [
            self::b64(json_encode($header)),
            self::b64(json_encode($payload)),
        ];
        $signingInput = implode('.', $segments);
        $signature = hash_hmac('sha256', $signingInput, self::secret(), true);
        $segments[] = self::b64($signature);

        return implode('.', $segments);
    }

    /**
     * @return array|null  decoded payload, or null when invalid/expired
     */
    public static function verify(?string $token): ?array
    {
        if (! $token) {
            return null;
        }
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }
        [$h, $p, $s] = $parts;
        $expected = self::b64(hash_hmac('sha256', "$h.$p", self::secret(), true));
        if (! hash_equals($expected, $s)) {
            return null;
        }
        $payload = json_decode(self::b64decode($p), true);
        if (! is_array($payload)) {
            return null;
        }
        if (isset($payload['exp']) && time() >= $payload['exp']) {
            return null;
        }
        return $payload;
    }

    public static function signAdmin(array $payload): string
    {
        return self::sign($payload, 60 * 60 * 8); // 8h
    }

    public static function signCustomer(array $payload): string
    {
        $payload['type'] = 'customer';
        return self::sign($payload, 60 * 60 * 24 * 30); // 30d
    }
}
