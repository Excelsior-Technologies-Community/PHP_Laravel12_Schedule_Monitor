<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleMonitorController extends Controller
{
    /**
     * Scheduler Monitoring Dashboard
     */
    public function dashboard()
    {
        $tasks = DB::table('monitored_scheduled_tasks')
            ->orderBy('name')
            ->get();

        $totalTasks = $tasks->count();

        $successfulTasks = $tasks->filter(function ($task) {
            return !empty($task->last_finished_at)
                && empty($task->last_failed_at);
        })->count();

        $failedTasks = $tasks->filter(function ($task) {
            return !empty($task->last_failed_at);
        })->count();

        $skippedTasks = $tasks->filter(function ($task) {
            return !empty($task->last_skipped_at);
        })->count();

        $lateTasks = $tasks->filter(function ($task) {
            if (!$task->last_started_at || !$task->last_finished_at) {
                return false;
            }

            $startedAt = Carbon::parse($task->last_started_at);
            $finishedAt = Carbon::parse($task->last_finished_at);

            return $finishedAt->greaterThan(
                $startedAt->copy()->addMinutes((int) $task->grace_time_in_minutes)
            );
        })->count();

        $neverRunTasks = $tasks->filter(function ($task) {
            return empty($task->last_started_at);
        })->count();

        $totalLogItems = DB::table('monitored_scheduled_task_log_items')
            ->count();

        $failedLogItems = DB::table('monitored_scheduled_task_log_items')
            ->where('type', 'failed')
            ->count();

        $skippedLogItems = DB::table('monitored_scheduled_task_log_items')
            ->where('type', 'skipped')
            ->count();

        $recentLogs = DB::table('monitored_scheduled_task_log_items as logs')
            ->join(
                'monitored_scheduled_tasks as tasks',
                'tasks.id',
                '=',
                'logs.monitored_scheduled_task_id'
            )
            ->select(
                'logs.id',
                'logs.type',
                'logs.meta',
                'logs.created_at',
                'tasks.name',
                'tasks.type as task_type'
            )
            ->orderByDesc('logs.created_at')
            ->limit(10)
            ->get();

        $runtimeStatistics = $tasks
            ->filter(function ($task) {
                return $task->last_started_at && $task->last_finished_at;
            })
            ->map(function ($task) {
                $startedAt = Carbon::parse($task->last_started_at);
                $finishedAt = Carbon::parse($task->last_finished_at);

                return [
                    'name' => $task->name ?: 'Unnamed Task',
                    'runtime' => $startedAt->diffInSeconds($finishedAt),
                ];
            });

        $averageRuntime = $runtimeStatistics->count()
            ? round($runtimeStatistics->avg('runtime'), 2)
            : 0;

        $longestRuntime = $runtimeStatistics->count()
            ? $runtimeStatistics->max('runtime')
            : 0;

        $latestTask = $tasks
            ->filter(fn ($task) => !empty($task->last_finished_at))
            ->sortByDesc('last_finished_at')
            ->first();

        return view('schedule-monitor.dashboard', compact(
            'tasks',
            'totalTasks',
            'successfulTasks',
            'failedTasks',
            'skippedTasks',
            'lateTasks',
            'neverRunTasks',
            'totalLogItems',
            'failedLogItems',
            'skippedLogItems',
            'recentLogs',
            'runtimeStatistics',
            'averageRuntime',
            'longestRuntime',
            'latestTask'
        ));
    }

    /**
     * Scheduler execution history with search/filter/pagination.
     */
    /**
     * Scheduler execution history with search/filter/pagination.
     */
    public function history(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = DB::table('monitored_scheduled_task_log_items as logs')
            ->join(
                'monitored_scheduled_tasks as tasks',
                'tasks.id',
                '=',
                'logs.monitored_scheduled_task_id'
            )
            ->select(
                'logs.id',
                'logs.type',
                'logs.meta',
                'logs.created_at',
                'tasks.id as task_id',
                'tasks.name',
                'tasks.type as task_type',
                'tasks.cron_expression'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('tasks.name', 'like', '%' . $search . '%')
                    ->orWhere('tasks.type', 'like', '%' . $search . '%')
                    ->orWhere('logs.type', 'like', '%' . $search . '%');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status Filter
        |--------------------------------------------------------------------------
        */

        if ($status) {
            $query->where('logs.type', $status);
        }

        /*
        |--------------------------------------------------------------------------
        | Date Filters
        |--------------------------------------------------------------------------
        */

        if ($dateFrom) {
            $query->whereDate('logs.created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('logs.created_at', '<=', $dateTo);
        }

        /*
        |--------------------------------------------------------------------------
        | Execution Logs
        |--------------------------------------------------------------------------
        */

        $logs = $query
            ->orderByDesc('logs.created_at')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Status Statistics
        |--------------------------------------------------------------------------
        |
        | Spatie Schedule Monitor stores the start event as "starting".
        |
        */

        $statusCounts = [
            'starting' => DB::table('monitored_scheduled_task_log_items')
                ->where('type', 'starting')
                ->count(),

            'finished' => DB::table('monitored_scheduled_task_log_items')
                ->where('type', 'finished')
                ->count(),

            'failed' => DB::table('monitored_scheduled_task_log_items')
                ->where('type', 'failed')
                ->count(),

            'skipped' => DB::table('monitored_scheduled_task_log_items')
                ->where('type', 'skipped')
                ->count(),
        ];

        return view('schedule-monitor.history', compact(
            'logs',
            'search',
            'status',
            'dateFrom',
            'dateTo',
            'statusCounts'
        ));
    }

    /**
     * Failure, skipped and late scheduler alert manager.
     */
    public function alerts()
    {
        $tasks = DB::table('monitored_scheduled_tasks')
            ->orderByDesc('last_failed_at')
            ->orderByDesc('last_skipped_at')
            ->get();

        $failedTasks = $tasks->filter(function ($task) {
            return !empty($task->last_failed_at);
        });

        $skippedTasks = $tasks->filter(function ($task) {
            return !empty($task->last_skipped_at);
        });

        $lateTasks = $tasks->filter(function ($task) {
            if (!$task->last_started_at || !$task->last_finished_at) {
                return false;
            }

            $startedAt = Carbon::parse($task->last_started_at);
            $finishedAt = Carbon::parse($task->last_finished_at);

            return $finishedAt->greaterThan(
                $startedAt->copy()->addMinutes((int) $task->grace_time_in_minutes)
            );
        });

        $neverRunTasks = $tasks->filter(function ($task) {
            return empty($task->last_started_at);
        });

        $alertLogs = DB::table('monitored_scheduled_task_log_items as logs')
            ->join(
                'monitored_scheduled_tasks as tasks',
                'tasks.id',
                '=',
                'logs.monitored_scheduled_task_id'
            )
            ->select(
                'logs.id',
                'logs.type',
                'logs.meta',
                'logs.created_at',
                'tasks.name',
                'tasks.type as task_type'
            )
            ->whereIn('logs.type', ['failed', 'skipped'])
            ->orderByDesc('logs.created_at')
            ->paginate(10)
            ->withQueryString();

        $failedCount = $failedTasks->count();
        $skippedCount = $skippedTasks->count();
        $lateCount = $lateTasks->count();
        $neverRunCount = $neverRunTasks->count();

        return view('schedule-monitor.alerts', compact(
            'failedTasks',
            'skippedTasks',
            'lateTasks',
            'neverRunTasks',
            'alertLogs',
            'failedCount',
            'skippedCount',
            'lateCount',
            'neverRunCount'
        ));
    }
}