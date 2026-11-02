<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Uptime check for monitors (UptimeRobot etc). No secrets; 503 only when the database is unreachable.
Route::get('health', function () {
    $checks = ['app' => true, 'database' => false, 'storage_writable' => false, 'last_backup_age_hours' => null, 'pending_migrations' => null];
    try {
        DB::select('select 1');
        $checks['database'] = true;
        $ran = DB::table('migrations')->pluck('migration')->all();
        $files = array_map(fn ($f) => basename($f, '.php'), glob(database_path('migrations/*.php')) ?: []);
        $checks['pending_migrations'] = count(array_diff($files, $ran));
    } catch (\Throwable) {
        // reported through the 'database' flag
    }
    $checks['storage_writable'] = is_writable(storage_path('logs')) && is_writable(storage_path('framework'));
    $backups = glob(storage_path('app/backups/*.sql.gz')) ?: [];
    if ($backups) {
        $checks['last_backup_age_hours'] = round((time() - max(array_map('filemtime', $backups))) / 3600, 1);
    }
    $ok = $checks['database'] && $checks['storage_writable'];

    return response()->json(['status' => $ok ? 'ok' : 'degraded', 'time' => now()->toIso8601String()] + $checks, $checks['database'] ? 200 : 503)
        ->header('Cache-Control', 'no-store');
})->middleware('throttle:public');
