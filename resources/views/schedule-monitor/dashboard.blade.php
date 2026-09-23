<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Scheduler Monitoring Dashboard</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
    <div class="container">

        <a class="navbar-brand fw-bold"
           href="{{ route('schedule-monitor.dashboard') }}">
            Laravel Schedule Monitor
        </a>

        <div class="d-flex gap-2">

            <a href="{{ route('schedule-monitor.dashboard') }}"
               class="btn btn-outline-light btn-sm">
                Dashboard
            </a>

            <a href="{{ route('schedule-monitor.history') }}"
               class="btn btn-outline-light btn-sm">
                Execution History
            </a>

            <a href="{{ route('schedule-monitor.alerts') }}"
               class="btn btn-outline-light btn-sm">
                Alerts
            </a>

        </div>
    </div>
</nav>

<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                Scheduler Monitoring Dashboard
            </h2>

            <p class="text-muted mb-0">
                Laravel scheduled task execution and runtime monitoring
            </p>
        </div>

        <a href="{{ route('schedule-monitor.history') }}"
           class="btn btn-primary">
            View Execution History
        </a>

    </div>


    {{-- Statistics Cards --}}

    <div class="row g-3 mb-4">

        <div class="col-md-4 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <h6 class="text-muted">
                        Monitored Tasks
                    </h6>

                    <h2 class="fw-bold">
                        {{ $totalTasks }}
                    </h2>

                    <small class="text-muted">
                        Registered scheduled tasks
                    </small>

                </div>
            </div>
        </div>


        <div class="col-md-4 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <h6 class="text-muted">
                        Successful Tasks
                    </h6>

                    <h2 class="fw-bold text-success">
                        {{ $successfulTasks }}
                    </h2>

                    <small class="text-muted">
                        Completed without failure
                    </small>

                </div>
            </div>
        </div>


        <div class="col-md-4 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <h6 class="text-muted">
                        Failed Tasks
                    </h6>

                    <h2 class="fw-bold text-danger">
                        {{ $failedTasks }}
                    </h2>

                    <small class="text-muted">
                        Tasks with recorded failures
                    </small>

                </div>
            </div>
        </div>


        <div class="col-md-4 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <h6 class="text-muted">
                        Skipped Tasks
                    </h6>

                    <h2 class="fw-bold text-warning">
                        {{ $skippedTasks }}
                    </h2>

                    <small class="text-muted">
                        Missed or skipped executions
                    </small>

                </div>
            </div>
        </div>


        <div class="col-md-4 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <h6 class="text-muted">
                        Late Tasks
                    </h6>

                    <h2 class="fw-bold text-warning">
                        {{ $lateTasks }}
                    </h2>

                    <small class="text-muted">
                        Exceeded configured grace time
                    </small>

                </div>
            </div>
        </div>


        <div class="col-md-4 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <h6 class="text-muted">
                        Never Executed
                    </h6>

                    <h2 class="fw-bold text-secondary">
                        {{ $neverRunTasks }}
                    </h2>

                    <small class="text-muted">
                        Tasks without an execution
                    </small>

                </div>
            </div>
        </div>


        <div class="col-md-4 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <h6 class="text-muted">
                        Total Log Events
                    </h6>

                    <h2 class="fw-bold">
                        {{ $totalLogItems }}
                    </h2>

                    <small class="text-muted">
                        Recorded scheduler events
                    </small>

                </div>
            </div>
        </div>


        <div class="col-md-4 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">

                    <h6 class="text-muted">
                        Average Runtime
                    </h6>

                    <h2 class="fw-bold">
                        {{ number_format($averageRuntime, 2) }}s
                    </h2>

                    <small class="text-muted">
                        Based on completed executions
                    </small>

                </div>
            </div>
        </div>

    </div>


    {{-- Runtime Summary --}}

    <div class="row g-4 mb-4">

        <div class="col-lg-6">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-header bg-white fw-bold">
                    Runtime Statistics
                </div>

                <div class="card-body">

                    <div class="row text-center">

                        <div class="col-6">

                            <h3 class="fw-bold">
                                {{ number_format($averageRuntime, 2) }}s
                            </h3>

                            <span class="text-muted">
                                Average Runtime
                            </span>

                        </div>

                        <div class="col-6">

                            <h3 class="fw-bold">
                                {{ number_format($longestRuntime, 2) }}s
                            </h3>

                            <span class="text-muted">
                                Longest Runtime
                            </span>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <div class="col-lg-6">

            <div class="card shadow-sm border-0 h-100">

                <div class="card-header bg-white fw-bold">
                    Latest Completed Task
                </div>

                <div class="card-body">

                    @if ($latestTask)

                        <h5 class="fw-bold">
                            {{ $latestTask->name ?: 'Unnamed Task' }}
                        </h5>

                        <p class="mb-1">
                            <strong>Started:</strong>
                            {{ \Carbon\Carbon::parse($latestTask->last_started_at)->format('Y-m-d H:i:s') }}
                        </p>

                        <p class="mb-0">
                            <strong>Finished:</strong>
                            {{ \Carbon\Carbon::parse($latestTask->last_finished_at)->format('Y-m-d H:i:s') }}
                        </p>

                    @else

                        <p class="text-muted mb-0">
                            No completed scheduler execution found.
                        </p>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- Task Overview --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white fw-bold">
            Monitored Task Overview
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-dark">

                        <tr>
                            <th>Task</th>
                            <th>Type</th>
                            <th>Schedule</th>
                            <th>Last Started</th>
                            <th>Last Finished</th>
                            <th>Status</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($tasks as $task)

                            @php
                                $status = 'Pending';

                                if ($task->last_failed_at) {
                                    $status = 'Failed';
                                } elseif ($task->last_skipped_at) {
                                    $status = 'Skipped';
                                } elseif ($task->last_finished_at) {
                                    $status = 'Successful';
                                }
                            @endphp

                            <tr>

                                <td class="fw-semibold">
                                    {{ $task->name ?: 'Unnamed Task' }}
                                </td>

                                <td>
                                    {{ $task->type ?: 'N/A' }}
                                </td>

                                <td>
                                    <code>
                                        {{ $task->cron_expression }}
                                    </code>
                                </td>

                                <td>
                                    {{ $task->last_started_at
                                        ? \Carbon\Carbon::parse($task->last_started_at)->format('Y-m-d H:i:s')
                                        : '--' }}
                                </td>

                                <td>
                                    {{ $task->last_finished_at
                                        ? \Carbon\Carbon::parse($task->last_finished_at)->format('Y-m-d H:i:s')
                                        : '--' }}
                                </td>

                                <td>

                                    @if ($status === 'Successful')
                                        <span class="badge bg-success">
                                            Successful
                                        </span>
                                    @elseif ($status === 'Failed')
                                        <span class="badge bg-danger">
                                            Failed
                                        </span>
                                    @elseif ($status === 'Skipped')
                                        <span class="badge bg-warning text-dark">
                                            Skipped
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            Pending
                                        </span>
                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6"
                                    class="text-center text-muted py-4">
                                    No monitored tasks found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- Recent Events --}}

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white fw-bold">
            Recent Scheduler Events
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead class="table-light">

                        <tr>
                            <th>Task</th>
                            <th>Event</th>
                            <th>Type</th>
                            <th>Time</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($recentLogs as $log)

                            <tr>

                                <td>
                                    {{ $log->name ?: 'Unnamed Task' }}
                                </td>

                                <td>

                                    @if ($log->type === 'failed')
                                        <span class="badge bg-danger">
                                            Failed
                                        </span>
                                    @elseif ($log->type === 'skipped')
                                        <span class="badge bg-warning text-dark">
                                            Skipped
                                        </span>
                                    @elseif ($log->type === 'finished')
                                        <span class="badge bg-success">
                                            Finished
                                        </span>
                                    @else
                                        <span class="badge bg-primary">
                                            {{ ucfirst($log->type) }}
                                        </span>
                                    @endif

                                </td>

                                <td>
                                    {{ $log->task_type ?: 'N/A' }}
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="4"
                                    class="text-center text-muted py-4">
                                    No scheduler events found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>