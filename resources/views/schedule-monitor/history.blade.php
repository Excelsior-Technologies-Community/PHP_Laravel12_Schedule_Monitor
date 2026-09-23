<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Scheduler Execution History</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

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
                    class="btn btn-light btn-sm">
                    History
                </a>

                <a href="{{ route('schedule-monitor.alerts') }}"
                    class="btn btn-outline-light btn-sm">
                    Alerts
                </a>

            </div>

        </div>

    </nav>


    <div class="container py-4">

        <div class="mb-4">

            <h2 class="fw-bold">
                Scheduler Execution History
            </h2>

            <p class="text-muted">
                Search and filter recorded scheduled task executions.
            </p>

        </div>


        {{-- Status Summary --}}

        <div class="row g-3 mb-4">

            <div class="col-md-3">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <small class="text-muted">
                            Started
                        </small>

                        <h3 class="fw-bold">
                            {{ $statusCounts['starting'] }}
                        </h3>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <small class="text-muted">
                            Finished
                        </small>

                        <h3 class="fw-bold text-success">
                            {{ $statusCounts['finished'] }}
                        </h3>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <small class="text-muted">
                            Failed
                        </small>

                        <h3 class="fw-bold text-danger">
                            {{ $statusCounts['failed'] }}
                        </h3>

                    </div>

                </div>

            </div>


            <div class="col-md-3">

                <div class="card shadow-sm border-0">

                    <div class="card-body">

                        <small class="text-muted">
                            Skipped
                        </small>

                        <h3 class="fw-bold text-warning">
                            {{ $statusCounts['skipped'] }}
                        </h3>

                    </div>

                </div>

            </div>

        </div>


        {{-- Search and Filters --}}

        <div class="card shadow-sm border-0 mb-4">

            <div class="card-header bg-white fw-bold">
                Search & Filter Scheduler Executions
            </div>

            <div class="card-body">

                <form method="GET"
                    action="{{ route('schedule-monitor.history') }}">

                    <div class="row g-3">

                        <div class="col-md-4">

                            <label class="form-label">
                                Search Task
                            </label>

                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                value="{{ $search }}"
                                placeholder="Search task name...">

                        </div>


                        <div class="col-md-2">

                            <label class="form-label">
                                Status
                            </label>

                            <select name="status"
                                class="form-select">

                                <option value="">
                                    All Statuses
                                </option>

                                <option value="starting"
                                    {{ $status === 'starting' ? 'selected' : '' }}>
                                    Started
                                </option>

                                <option value="finished"
                                    {{ $status === 'finished' ? 'selected' : '' }}>
                                    Finished
                                </option>

                                <option value="failed"
                                    {{ $status === 'failed' ? 'selected' : '' }}>
                                    Failed
                                </option>

                                <option value="skipped"
                                    {{ $status === 'skipped' ? 'selected' : '' }}>
                                    Skipped
                                </option>

                            </select>

                        </div>


                        <div class="col-md-2">

                            <label class="form-label">
                                From Date
                            </label>

                            <input
                                type="date"
                                name="date_from"
                                class="form-control"
                                value="{{ $dateFrom }}">

                        </div>


                        <div class="col-md-2">

                            <label class="form-label">
                                To Date
                            </label>

                            <input
                                type="date"
                                name="date_to"
                                class="form-control"
                                value="{{ $dateTo }}">

                        </div>


                        <div class="col-md-2 d-flex align-items-end">

                            <button class="btn btn-primary w-100"
                                type="submit">
                                Search
                            </button>

                        </div>

                    </div>

                </form>


                <div class="mt-3">

                    <a href="{{ route('schedule-monitor.history') }}"
                        class="btn btn-outline-secondary btn-sm">
                        Clear Filters
                    </a>

                </div>

            </div>

        </div>


        {{-- Execution History Table --}}

        <div class="card shadow-sm border-0">

            <div class="card-header bg-white fw-bold">
                Execution Events
            </div>

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-dark">

                            <tr>
                                <th>#</th>
                                <th>Task</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Cron Expression</th>
                                <th>Executed At</th>
                            </tr>

                        </thead>

                        <tbody>

                            @forelse ($logs as $log)

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

                                    @if ($log->type === 'finished')

                                    <span class="badge bg-success">
                                        Finished
                                    </span>

                                    @elseif ($log->type === 'failed')

                                    <span class="badge bg-danger">
                                        Failed
                                    </span>

                                    @elseif ($log->type === 'skipped')

                                    <span class="badge bg-warning text-dark">
                                        Skipped
                                    </span>

                                    @elseif ($log->type === 'starting')

                                    <span class="badge bg-primary">
                                        Started
                                    </span>

                                    @else

                                    <span class="badge bg-secondary">
                                        {{ ucfirst($log->type) }}
                                    </span>

                                    @endif

                                </td>

                                <td>
                                    <code>
                                        {{ $log->cron_expression }}
                                    </code>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                                </td>

                            </tr>

                            @empty

                            <tr>

                                <td colspan="6"
                                    class="text-center text-muted py-5">

                                    No execution history found.

                                </td>

                            </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>


            @if ($logs->hasPages())

            <div class="card-footer bg-white">

                {{ $logs->links('pagination::bootstrap-5') }}

            </div>

            @endif

        </div>

    </div>

</body>

</html>