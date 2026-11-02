<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Support\Access;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    private function scoped(Request $request)
    {
        $branch = Access::branchScope($request);

        return Reservation::query()->when($branch, fn ($q) => $q->where('reservations.branch_id', $branch));
    }

    public function index(Request $request)
    {
        $q = $this->scoped($request)->leftJoin('stock_items', 'stock_items.id', '=', 'reservations.stock_item_id')
            ->leftJoin('branches', 'branches.id', '=', 'reservations.branch_id')
            ->select('reservations.*', 'stock_items.name as item_name', 'stock_items.model_number', 'branches.name as branch_name');
        if ($s = $request->query('status')) {
            $s === 'active' ? $q->whereIn('reservations.status', ['pending', 'confirmed']) : $q->where('reservations.status', $s);
        }
        if ($search = trim((string) $request->query('search', ''))) {
            $q->where(fn ($w) => $w->where('reservations.code', 'like', "%$search%")->orWhere('reservations.name', 'like', "%$search%")->orWhere('reservations.phone', 'like', "%$search%"));
        }

        return $this->data($q->orderByRaw("FIELD(reservations.status,'pending','confirmed','collected','cancelled','expired')")->orderByDesc('reservations.id')->limit(300)->get());
    }

    /** Navigation badge: how many holds are waiting for staff. */
    public function pendingCount(Request $request)
    {
        return $this->data(['pending' => $this->scoped($request)->where('reservations.status', 'pending')->where('reservations.expires_at', '>', now())->count()]);
    }

    public function confirm(Request $request, $id)
    {
        return $this->transition($request, $id, ['pending'], 'confirmed', ['expires_at' => now()->addHours(48)]);
    }

    public function collect(Request $request, $id)
    {
        return $this->transition($request, $id, ['pending', 'confirmed'], 'collected');
    }

    public function cancel(Request $request, $id)
    {
        return $this->transition($request, $id, ['pending', 'confirmed'], 'cancelled');
    }

    private function transition(Request $request, $id, array $from, string $to, array $extra = [])
    {
        $row = $this->scoped($request)->where('reservations.id', $id)->first();
        if (! $row) {
            return $this->notFound();
        }
        if (! in_array($row->status, $from, true)) {
            return $this->message("A {$row->status} reservation cannot be changed to {$to}.", 422);
        }
        $row->update(['status' => $to] + $extra);

        return $this->data($row->fresh());
    }
}
