<?php

namespace App\Support;

/** RFC 6238 time-based one-time passwords (Google Authenticator / Authy compatible). */
class Totp
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function generateSecret(int $bytes = 20): string
    {
        $bin = random_bytes($bytes);
        $bits = '';
        foreach (str_split($bin) as $c) {
            $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 5) as $chunk) {
            $out .= self::ALPHABET[bindec(str_pad($chunk, 5, '0'))];
        }

        return $out;
    }

    private static function decode(string $b32): string
    {
        $bits = '';
        foreach (str_split(strtoupper(preg_replace('/[^A-Z2-7]/i', '', $b32))) as $c) {
            $bits .= str_pad(decbin(strpos(self::ALPHABET, $c)), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 8) as $byte) {
            if (strlen($byte) === 8) {
                $out .= chr(bindec($byte));
            }
        }

        return $out;
    }

    public static function code(string $secret, ?int $time = null): string
    {
        $counter = intdiv($time ?? time(), 30);
        $hash = hash_hmac('sha1', pack('N*', 0, $counter), self::decode($secret), true);
        $offset = ord($hash[19]) & 0xf;
        $n = ((ord($hash[$offset]) & 0x7f) << 24) | (ord($hash[$offset + 1]) << 16) | (ord($hash[$offset + 2]) << 8) | ord($hash[$offset + 3]);

        return str_pad((string) ($n % 1000000), 6, '0', STR_PAD_LEFT);
    }

    /** Accepts the current code and one step either side to tolerate clock drift. */
    public static function verify(string $secret, string $code): bool
    {
        $code = preg_replace('/\s+/', '', $code);
        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }
        $t = time();
        foreach ([-30, 0, 30] as $drift) {
            if (hash_equals(self::code($secret, $t + $drift), $code)) {
                return true;
            }
        }

        return false;
    }

    public static function uri(string $secret, string $email, string $issuer = 'Trinetraa Optician'): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $email) . '?secret=' . $secret . '&issuer=' . rawurlencode($issuer) . '&digits=6&period=30';
    }
}
