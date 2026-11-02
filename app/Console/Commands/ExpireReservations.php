<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;

class ExpireReservations extends Command
{
    protected $signature = 'reservations:expire';

    protected $description = 'Release store-pickup reservations whose hold time has passed';

    public function handle(): int
    {
        $n = Reservation::whereIn('status', ['pending', 'confirmed'])->where('expires_at', '<=', now())->update(['status' => 'expired']);
        $this->info("Expired {$n} reservation(s).");

        return self::SUCCESS;
    }
}
