<?php

namespace App\Support;

class Slug
{
    public static function generate(string $name): string
    {
        $s = strtolower(trim($name));
        $s = preg_replace('/[^a-z0-9\s-]/', '', $s);
        $s = preg_replace('/\s+/', '-', $s);
        $s = preg_replace('/-+/', '-', $s);
        return $s;
    }
}
