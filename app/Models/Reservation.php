<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** Store-pickup hold placed from the public website. Does not touch stock; it only reduces what can be reserved. */
class Reservation extends Model
{
    protected $guarded = [];

    protected $casts = ['expires_at' => 'datetime', 'quantity' => 'integer'];

    /** Holds that still count against availability. */
    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', ['pending', 'confirmed'])->where('expires_at', '>', now());
    }

    /** Units of an item at a branch currently held by active reservations. */
    public static function heldQty(int $stockItemId, int $branchId): int
    {
        return (int) static::active()->where('stock_item_id', $stockItemId)->where('branch_id', $branchId)->sum('quantity');
    }

    public static function newCode(): string
    {
        do {
            $code = 'RSV-' . strtoupper(substr(str_replace(['0', 'O', '1', 'I'], '', bin2hex(random_bytes(8))), 0, 6));
        } while (strlen($code) < 10 || static::where('code', $code)->exists());

        return $code;
    }
}
