<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar recordatorios de citas diarias a las 7:00 AM
Schedule::command('citas:recordatorio')
    ->dailyAt('07:00')
    ->withoutOverlapping()
    ->runInBackground();

// También programar recordatorios a las 8:30 AM (30 min antes del trabajo)
Schedule::command('citas:recordatorio')
    ->dailyAt('08:30')
    ->withoutOverlapping()
    ->runInBackground();
