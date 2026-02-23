<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Console\Scheduling\Event;

/*
|--------------------------------------------------------------------------
| Test Command
|--------------------------------------------------------------------------
*/

Artisan::command('test:scheduler', function () {
    $this->info('Scheduler running at '.now());
});

/*
|--------------------------------------------------------------------------
| Scheduler (SAFE FOR LARAVEL 12)
|--------------------------------------------------------------------------
*/

$schedule = Schedule::command('test:scheduler')
    ->everyMinute();

/*
|--------------------------------------------------------------------------
| Only attach monitor() if macro exists
|--------------------------------------------------------------------------
*/

if (method_exists(Event::class, 'monitor')) {
    $schedule->monitor();
}