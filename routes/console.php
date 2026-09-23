<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Console\Scheduling\Event;

/*
|--------------------------------------------------------------------------
| Scheduler
|--------------------------------------------------------------------------
*/

$schedule = Schedule::command('test:scheduler')
    ->everyMinute();

/*
|--------------------------------------------------------------------------
| Schedule Monitor
|--------------------------------------------------------------------------
*/

if (method_exists(Event::class, 'monitor')) {
    $schedule->monitor();
}