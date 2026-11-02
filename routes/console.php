<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily database backup (cPanel cron: * * * * * php /home/<user>/trinetraa_app/artisan schedule:run)
Illuminate\Support\Facades\Schedule::command('db:backup --keep=14')->dailyAt('02:30');

// Release lapsed store-pickup holds every 15 minutes.
Illuminate\Support\Facades\Schedule::command('reservations:expire')->everyFifteenMinutes();

// Daily customer reminders (eye-test follow-ups, birthdays, lens renewals, review requests)
Illuminate\Support\Facades\Schedule::command('reminders:send')->dailyAt('10:00');
