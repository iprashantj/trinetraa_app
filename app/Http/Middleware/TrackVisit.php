<?php

namespace App\Http\Middleware;

use App\Models\SiteVisit;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Counts one visit per session per day against public-facing pages only,
 * so admins can see traffic in the admin footer without a full analytics setup.
 */
class TrackVisit
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldTrack($request)) {
            $today = Carbon::today()->toDateString();
            $sessionKey = 'visit_counted_' . $today;

            if (! $request->session()->get($sessionKey)) {
                $this->increment($today);
                $request->session()->put($sessionKey, true);
            }
        }

        return $next($request);
    }

    private function shouldTrack(Request $request): bool
    {
        if (! $request->isMethod('GET')) {
            return false;
        }

        return ! $request->is('admin*', 'account*', 'sitemap*.xml', 'robots.txt', '_*');
    }

    private function increment(string $date): void
    {
        $affected = SiteVisit::where('visit_date', $date)->increment('count');
        if ($affected === 0) {
            try {
                SiteVisit::create(['visit_date' => $date, 'count' => 1]);
            } catch (\Throwable $e) {
                // Concurrent request already created today's row — just increment it.
                SiteVisit::where('visit_date', $date)->increment('count');
            }
        }
    }
}
