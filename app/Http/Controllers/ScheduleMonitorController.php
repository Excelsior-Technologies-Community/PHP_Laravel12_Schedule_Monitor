<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                $startedAt->copy()->addMinutes(
                    (int) $task->grace_time_in_minutes
                )
            );
        })->count();

        $neverRunTasks = $tasks->filter(function ($task) {
            return empty($task->last_started_at);
        })->count();

        $totalLogItems = DB::table(
            'monitored_scheduled_task_log_items'
        )->count();

        $failedLogItems = DB::table(
            'monitored_scheduled_task_log_items'
        )
            ->where('type', 'failed')
            ->count();

        $skippedLogItems = DB::table(
            'monitored_scheduled_task_log_items'
        )
            ->where('type', 'skipped')
            ->count();

        $recentLogs = DB::table(
            'monitored_scheduled_task_log_items as logs'
        )
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
            ->limit(5)
            ->get();

        $runtimeStatistics = $tasks
            ->filter(function ($task) {
                return $task->last_started_at
                    && $task->last_finished_at;
            })
            ->map(function ($task) {
                $startedAt = Carbon::parse(
                    $task->last_started_at
                );

                $finishedAt = Carbon::parse(
                    $task->last_finished_at
                );

                return [
                    'name' => $task->name ?: 'Unnamed Task',
                    'runtime' => $startedAt->diffInSeconds(
                        $finishedAt
                    ),
                ];
            });

        $averageRuntime = $runtimeStatistics->count()
            ? round($runtimeStatistics->avg('runtime'), 2)
            : 0;

        $longestRuntime = $runtimeStatistics->count()
            ? $runtimeStatistics->max('runtime')
            : 0;

        $latestTask = $tasks
            ->filter(function ($task) {
                return !empty($task->last_finished_at);
            })
            ->sortByDesc('last_finished_at')
            ->first();

        return view(
            'schedule-monitor.dashboard',
            compact(
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
            )
        );
    }

    /**
     * Scheduler execution history.
     *
     * Added functionality:
     *
     * 1. Search
     * 2. Status filter
     * 3. Task type filter
     * 4. From date
     * 5. To date
     * 6. Today filter
     * 7. This month filter
     * 8. Last 7 days filter
     * 9. Date sorting
     * 10. Records per page
     * 11. Filtered count
     * 12. CSV export
     */
    public function history(Request $request)
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $status = $request->input('status');

        $taskType = $request->input('task_type');

        $dateFrom = $request->input('date_from');

        $dateTo = $request->input('date_to');

        $quickFilter = $request->input('quick_filter');

        $sort = $request->input('sort', 'asc');

        $perPage = (int) $request->input(
            'per_page',
            5
        );

        /*
        |--------------------------------------------------------------------------
        | Allowed Values
        |--------------------------------------------------------------------------
        */

        $allowedStatuses = [
            'starting',
            'finished',
            'failed',
            'skipped',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = null;
        }

        $allowedSorts = [
            'asc',
            'desc',
        ];

        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'asc';
        }

        $allowedPerPage = [
            5,
            10,
            25,
            50,
        ];

        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 5;
        }

        /*
        |--------------------------------------------------------------------------
        | Quick Date Filters
        |--------------------------------------------------------------------------
        */

        if ($quickFilter === 'today') {
            $dateFrom = now()->format('Y-m-d');
            $dateTo = now()->format('Y-m-d');
        }

        if ($quickFilter === 'this_month') {
            $dateFrom = now()
                ->startOfMonth()
                ->format('Y-m-d');

            $dateTo = now()
                ->endOfMonth()
                ->format('Y-m-d');
        }

        if ($quickFilter === 'last_7_days') {
            $dateFrom = now()
                ->subDays(6)
                ->format('Y-m-d');

            $dateTo = now()->format('Y-m-d');
        }

        /*
        |--------------------------------------------------------------------------
        | Main Query
        |--------------------------------------------------------------------------
        */

        $query = DB::table(
            'monitored_scheduled_task_log_items as logs'
        )
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

        /*
        |--------------------------------------------------------------------------
        | 1. Search
        |--------------------------------------------------------------------------
        */

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where(
                    'tasks.name',
                    'like',
                    '%' . $search . '%'
                )
                    ->orWhere(
                        'tasks.type',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'logs.type',
                        'like',
                        '%' . $search . '%'
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Status Filter
        |--------------------------------------------------------------------------
        */

        if ($status) {
            $query->where(
                'logs.type',
                $status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Task Type Filter
        |--------------------------------------------------------------------------
        */

        if ($taskType) {
            $query->where(
                'tasks.type',
                $taskType
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 4. From Date
        |--------------------------------------------------------------------------
        */

        if ($dateFrom) {
            $query->whereDate(
                'logs.created_at',
                '>=',
                $dateFrom
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 5. To Date
        |--------------------------------------------------------------------------
        */

        if ($dateTo) {
            $query->whereDate(
                'logs.created_at',
                '<=',
                $dateTo
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 9. Date Sorting
        |--------------------------------------------------------------------------
        */

     $query->orderBy('logs.id', 'asc');

        /*
        |--------------------------------------------------------------------------
        | 11. Filtered Count
        |--------------------------------------------------------------------------
        */

        $filteredCount = (clone $query)->count();

        /*
        |--------------------------------------------------------------------------
        | 10. Pagination
        |--------------------------------------------------------------------------
        */

        $logs = $query
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Status Statistics
        |--------------------------------------------------------------------------
        */

        $statusCounts = [
            'starting' => DB::table(
                'monitored_scheduled_task_log_items'
            )
                ->where('type', 'starting')
                ->count(),

            'finished' => DB::table(
                'monitored_scheduled_task_log_items'
            )
                ->where('type', 'finished')
                ->count(),

            'failed' => DB::table(
                'monitored_scheduled_task_log_items'
            )
                ->where('type', 'failed')
                ->count(),

            'skipped' => DB::table(
                'monitored_scheduled_task_log_items'
            )
                ->where('type', 'skipped')
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | Task Type Dropdown
        |--------------------------------------------------------------------------
        */

        $taskTypes = DB::table(
            'monitored_scheduled_tasks'
        )
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view(
            'schedule-monitor.history',
            compact(
                'logs',
                'search',
                'status',
                'taskType',
                'dateFrom',
                'dateTo',
                'quickFilter',
                'sort',
                'perPage',
                'filteredCount',
                'statusCounts',
                'taskTypes'
            )
        );
    }

    /**
     * Export filtered scheduler history as CSV.
     */
    public function exportHistory(
        Request $request
    ): StreamedResponse {
        $search = trim(
            (string) $request->input('search', '')
        );

        $status = $request->input('status');

        $taskType = $request->input('task_type');

        $dateFrom = $request->input('date_from');

        $dateTo = $request->input('date_to');

        $quickFilter = $request->input('quick_filter');

        $sort = $request->input('sort', 'asc');

        /*
        |--------------------------------------------------------------------------
        | Validate status
        |--------------------------------------------------------------------------
        */

        $allowedStatuses = [
            'starting',
            'finished',
            'failed',
            'skipped',
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            $status = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Validate sorting
        |--------------------------------------------------------------------------
        */

        if (!in_array($sort, ['asc', 'desc'], true)) {
            $sort = 'asc';
        }

        /*
        |--------------------------------------------------------------------------
        | Quick Filters
        |--------------------------------------------------------------------------
        */

        if ($quickFilter === 'today') {
            $dateFrom = now()->format('Y-m-d');
            $dateTo = now()->format('Y-m-d');
        }

        if ($quickFilter === 'this_month') {
            $dateFrom = now()
                ->startOfMonth()
                ->format('Y-m-d');

            $dateTo = now()
                ->endOfMonth()
                ->format('Y-m-d');
        }

        if ($quickFilter === 'last_7_days') {
            $dateFrom = now()
                ->subDays(6)
                ->format('Y-m-d');

            $dateTo = now()->format('Y-m-d');
        }

        /*
        |--------------------------------------------------------------------------
        | Export Query
        |--------------------------------------------------------------------------
        */

        $query = DB::table(
            'monitored_scheduled_task_log_items as logs'
        )
            ->join(
                'monitored_scheduled_tasks as tasks',
                'tasks.id',
                '=',
                'logs.monitored_scheduled_task_id'
            )
            ->select(
                'logs.id',
                'logs.type',
                'logs.created_at',
                'tasks.id as task_id',
                'tasks.name',
                'tasks.type as task_type',
                'tasks.cron_expression'
            );

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where(
                    'tasks.name',
                    'like',
                    '%' . $search . '%'
                )
                    ->orWhere(
                        'tasks.type',
                        'like',
                        '%' . $search . '%'
                    )
                    ->orWhere(
                        'logs.type',
                        'like',
                        '%' . $search . '%'
                    );
            });
        }

        if ($status) {
            $query->where(
                'logs.type',
                $status
            );
        }

        if ($taskType) {
            $query->where(
                'tasks.type',
                $taskType
            );
        }

        if ($dateFrom) {
            $query->whereDate(
                'logs.created_at',
                '>=',
                $dateFrom
            );
        }

        if ($dateTo) {
            $query->whereDate(
                'logs.created_at',
                '<=',
                $dateTo
            );
        }

      $query->orderBy('logs.id', 'asc');

        $filename = 'schedule-history-' .
            now()->format('Y-m-d-H-i-s') .
            '.csv';

        return response()->streamDownload(
            function () use ($query) {
                $handle = fopen(
                    'php://output',
                    'w'
                );

                fputcsv($handle, [
                    'ID',
                    'Task ID',
                    'Task Name',
                    'Task Type',
                    'Status',
                    'Cron Expression',
                    'Executed At',
                ]);

                $query->chunk(
                    500,
                    function ($logs) use ($handle) {
                        foreach ($logs as $log) {
                            fputcsv($handle, [
                                $log->id,
                                $log->task_id,
                                $log->name
                                    ?: 'Unnamed Task',
                                $log->task_type
                                    ?: 'N/A',
                                $log->type,
                                $log->cron_expression
                                    ?: 'N/A',
                                $log->created_at,
                            ]);
                        }
                    }
                );

                fclose($handle);
            },
            $filename,
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',
            ]
        );
    }

    /**
     * Failure, skipped and late scheduler alert manager.
     */
    public function alerts()
    {
        $tasks = DB::table(
            'monitored_scheduled_tasks'
        )
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
            if (!$task->last_started_at
                || !$task->last_finished_at) {
                return false;
            }

            $startedAt = Carbon::parse(
                $task->last_started_at
            );

            $finishedAt = Carbon::parse(
                $task->last_finished_at
            );

            return $finishedAt->greaterThan(
                $startedAt->copy()->addMinutes(
                    (int) $task->grace_time_in_minutes
                )
            );
        });

        $neverRunTasks = $tasks->filter(function ($task) {
            return empty($task->last_started_at);
        });

        $alertLogs = DB::table(
            'monitored_scheduled_task_log_items as logs'
        )
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
            ->whereIn(
                'logs.type',
                ['failed', 'skipped']
            )
            ->orderByDesc('logs.created_at')
            ->paginate(5)
            ->withQueryString();

        $failedCount = $failedTasks->count();

        $skippedCount = $skippedTasks->count();

        $lateCount = $lateTasks->count();

        $neverRunCount = $neverRunTasks->count();

        return view(
            'schedule-monitor.alerts',
            compact(
                'failedTasks',
                'skippedTasks',
                'lateTasks',
                'neverRunTasks',
                'alertLogs',
                'failedCount',
                'skippedCount',
                'lateCount',
                'neverRunCount'
            )
        );
    }
}