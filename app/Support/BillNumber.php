<?php

namespace App\Support;

class BillNumber
{
    public static function generate(string $prefix = 'BILL'): string
    {
        $y = date('Y');
        $m = date('m');
        $d = date('d');
        $rand = random_int(1000, 9999);
        return "{$prefix}-{$y}{$m}{$d}-{$rand}";
    }
}
