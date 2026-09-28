<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Captcha
{
    const TTL_SECONDS = 600; // 10 minutes

    public static function create(): array
    {
        $a = random_int(1, 10);
        $b = random_int(1, 10);
        $token = time() . '_' . Str::random(16);
        Cache::put('captcha:' . $token, $a + $b, self::TTL_SECONDS);
        return ['token' => $token, 'question' => "What is {$a} + {$b}?"];
    }

    public static function verify(?string $token, $answer): bool
    {
        if (! $token) {
            return false;
        }
        $key = 'captcha:' . $token;
        $expected = Cache::get($key);
        if ($expected === null) {
            return false;
        }
        Cache::forget($key);
        return (int) $answer === (int) $expected;
    }
}
