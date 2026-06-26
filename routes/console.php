<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('flags:check')->daily();
Schedule::command('reminders:send')->hourly();
Schedule::command('deployments:auto-complete')->daily();
Schedule::command('attendance:auto-resolve')->daily();
