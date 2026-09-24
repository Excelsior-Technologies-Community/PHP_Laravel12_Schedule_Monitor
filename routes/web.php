<?php

use App\Http\Controllers\ScheduleMonitorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Welcome
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Schedule Monitor
|--------------------------------------------------------------------------
*/

Route::prefix('schedule-monitor')
    ->name('schedule-monitor.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */

        Route::get('/dashboard', [
            ScheduleMonitorController::class,
            'dashboard',
        ])->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Execution History
        |--------------------------------------------------------------------------
        */

        Route::get('/history', [
            ScheduleMonitorController::class,
            'history',
        ])->name('history');

        /*
        |--------------------------------------------------------------------------
        | CSV Export
        |--------------------------------------------------------------------------
        */

        Route::get('/history/export', [
            ScheduleMonitorController::class,
            'exportHistory',
        ])->name('history.export');

        /*
        |--------------------------------------------------------------------------
        | Alerts
        |--------------------------------------------------------------------------
        */

        Route::get('/alerts', [
            ScheduleMonitorController::class,
            'alerts',
        ])->name('alerts');
    });