<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule product imports to run hourly
Schedule::command('products:import')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->runInBackground();

Schedule::command('embedding:generate-all')
    ->dailyAt('00:00')
    ->withoutOverlapping()
    ->runInBackground();