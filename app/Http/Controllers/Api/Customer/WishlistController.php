<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\WishlistItem;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->attributes->get('customer');

        $rows = WishlistItem::query()
            ->where('customer_id', $customer['id'])
            ->orderByDesc('added_at')
            ->with(['eyewear.category:id,name,slug'])
            ->get();

        return $this->data($rows);
    }

    public function store(Request $request)
    {
        $customer = $request->attributes->get('customer');
        $eyewearId = $request->input('eyewear_id');

        $existing = WishlistItem::query()
            ->where('customer_id', $customer['id'])
            ->where('eyewear_id', $eyewearId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['message' => 'Removed from wishlist.', 'added' => false]);
        }

        WishlistItem::create([
            'customer_id' => $customer['id'],
            'eyewear_id' => $eyewearId,
        ]);

        return response()->json(['message' => 'Added to wishlist.', 'added' => true], 201);
    }

    public function destroy(Request $request, $eyewearId)
    {
        $customer = $request->attributes->get('customer');

        WishlistItem::query()
            ->where('customer_id', $customer['id'])
            ->where('eyewear_id', (int) $eyewearId)
            ->delete();

        return $this->message('Removed from wishlist.');
    }

    public function check(Request $request, $eyewearId)
    {
        $customer = $request->attributes->get('customer');

        $item = WishlistItem::query()
            ->where('customer_id', $customer['id'])
            ->where('eyewear_id', (int) $eyewearId)
            ->first();

        return $this->data(['inWishlist' => (bool) $item]);
    }
}
