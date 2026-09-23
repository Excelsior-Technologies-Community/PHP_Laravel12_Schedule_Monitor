<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Scheduler Alerts</title>

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
                History
            </a>

            <a href="{{ route('schedule-monitor.alerts') }}"
               class="btn btn-light btn-sm">
                Alerts
            </a>

        </div>

    </div>

</nav>


<div class="container py-4">

    <div class="mb-4">

        <h2 class="fw-bold">
            Scheduler Failure & Missed-Run Alert Manager
        </h2>

        <p class="text-muted">
            Monitor failed, skipped, late and never-executed scheduled tasks.
        </p>

    </div>


    {{-- Alert Statistics --}}

    <div class="row g-3 mb-4">

        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <small class="text-muted">
                        Failed Tasks
                    </small>

                    <h2 class="fw-bold text-danger">
                        {{ $failedCount }}
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <small class="text-muted">
                        Skipped Tasks
                    </small>

                    <h2 class="fw-bold text-warning">
                        {{ $skippedCount }}
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <small class="text-muted">
                        Late Tasks
                    </small>

                    <h2 class="fw-bold text-warning">
                        {{ $lateCount }}
                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-3">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <small class="text-muted">
                        Never Executed
                    </small>

                    <h2 class="fw-bold text-secondary">
                        {{ $neverRunCount }}
                    </h2>

                </div>

            </div>

        </div>

    </div>


    {{-- Failed Tasks --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-danger text-white fw-bold">
            Failed Scheduler Tasks
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th>Task</th>
                            <th>Type</th>
                            <th>Last Failed</th>
                            <th>Last Started</th>
                            <th>Last Finished</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($failedTasks as $task)

                            <tr>

                                <td class="fw-semibold">
                                    {{ $task->name ?: 'Unnamed Task' }}
                                </td>

                                <td>
                                    {{ $task->type ?: 'N/A' }}
                                </td>

                                <td class="text-danger fw-semibold">
                                    {{ \Carbon\Carbon::parse($task->last_failed_at)->format('Y-m-d H:i:s') }}
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

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5"
                                    class="text-center text-muted py-4">

                                    No failed scheduler tasks.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- Skipped Tasks --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-warning fw-bold">
            Skipped / Missed Scheduler Tasks
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th>Task</th>
                            <th>Type</th>
                            <th>Last Skipped</th>
                            <th>Grace Time</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($skippedTasks as $task)

                            <tr>

                                <td class="fw-semibold">
                                    {{ $task->name ?: 'Unnamed Task' }}
                                </td>

                                <td>
                                    {{ $task->type ?: 'N/A' }}
                                </td>

                                <td class="text-warning fw-semibold">
                                    {{ \Carbon\Carbon::parse($task->last_skipped_at)->format('Y-m-d H:i:s') }}
                                </td>

                                <td>
                                    {{ $task->grace_time_in_minutes }} minutes
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="4"
                                    class="text-center text-muted py-4">

                                    No skipped scheduler tasks.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- Late Tasks --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-info text-white fw-bold">
            Late Scheduler Tasks
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th>Task</th>
                            <th>Started</th>
                            <th>Finished</th>
                            <th>Grace Time</th>
                            <th>Runtime</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($lateTasks as $task)

                            @php

                                $startedAt = \Carbon\Carbon::parse(
                                    $task->last_started_at
                                );

                                $finishedAt = \Carbon\Carbon::parse(
                                    $task->last_finished_at
                                );

                                $runtime = $startedAt->diffInSeconds(
                                    $finishedAt
                                );

                            @endphp

                            <tr>

                                <td class="fw-semibold">
                                    {{ $task->name ?: 'Unnamed Task' }}
                                </td>

                                <td>
                                    {{ $startedAt->format('Y-m-d H:i:s') }}
                                </td>

                                <td>
                                    {{ $finishedAt->format('Y-m-d H:i:s') }}
                                </td>

                                <td>
                                    {{ $task->grace_time_in_minutes }} minutes
                                </td>

                                <td>

                                    <span class="badge bg-warning text-dark">
                                        {{ $runtime }} seconds
                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5"
                                    class="text-center text-muted py-4">

                                    No late scheduler tasks detected.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- Never Run Tasks --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-secondary text-white fw-bold">
            Never Executed Tasks
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th>Task</th>
                            <th>Type</th>
                            <th>Cron Expression</th>
                            <th>Grace Time</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($neverRunTasks as $task)

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
                                    {{ $task->grace_time_in_minutes }} minutes
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="4"
                                    class="text-center text-muted py-4">

                                    All monitored tasks have execution history.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- Alert History --}}

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white fw-bold">
            Failure & Missed-Run Event History
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-dark">

                        <tr>
                            <th>#</th>
                            <th>Task</th>
                            <th>Type</th>
                            <th>Alert</th>
                            <th>Recorded At</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($alertLogs as $log)

                            <tr>

                                <td>
                                    {{ $log->id }}
                                </td>

                                <td class="fw-semibold">
                                    {{ $log->name ?: 'Unnamed Task' }}
                                </td>

                                <td>
                                    {{ $log->task_type ?: 'N/A' }}
                                </td>

                                <td>

                                    @if ($log->type === 'failed')

                                        <span class="badge bg-danger">
                                            Failed
                                        </span>

                                    @elseif ($log->type === 'skipped')

                                        <span class="badge bg-warning text-dark">
                                            Skipped / Missed
                                        </span>

                                    @else

                                        <span class="badge bg-secondary">
                                            {{ ucfirst($log->type) }}
                                        </span>

                                    @endif

                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="5"
                                    class="text-center text-muted py-4">

                                    No failure or missed-run events found.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        @if ($alertLogs->hasPages())

            <div class="card-footer bg-white">

                {{ $alertLogs->links('pagination::bootstrap-5') }}

            </div>

        @endif

    </div>

</div>

</body>

</html>