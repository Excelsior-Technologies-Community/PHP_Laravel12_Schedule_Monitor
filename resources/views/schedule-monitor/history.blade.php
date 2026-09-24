<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Scheduler Execution History</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="{{ route('schedule-monitor.dashboard') }}"
        >
            Laravel Schedule Monitor
        </a>

        <div class="d-flex gap-2">

            <a
                href="{{ route('schedule-monitor.dashboard') }}"
                class="btn btn-outline-light btn-sm"
            >
                Dashboard
            </a>

            <a
                href="{{ route('schedule-monitor.history') }}"
                class="btn btn-light btn-sm"
            >
                History
            </a>

            <a
                href="{{ route('schedule-monitor.alerts') }}"
                class="btn btn-outline-light btn-sm"
            >
                Alerts
            </a>

        </div>

    </div>

</nav>


<div class="container py-4">

    <div class="d-flex justify-content-between
                align-items-center mb-4">

        <div>

            <h2 class="fw-bold mb-1">
                Scheduler Execution History
            </h2>

            <p class="text-muted mb-0">
                Search, filter, sort and export
                scheduler execution history.
            </p>

        </div>

        {{-- CSV Export --}}

        <a
            href="{{ route(
                'schedule-monitor.history.export',
                request()->query()
            ) }}"
            class="btn btn-success"
        >
            📥 Export CSV
        </a>

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


    {{-- Filter Card --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-header bg-white fw-bold">

            🔎 Search & Filter Scheduler Executions

        </div>


        <div class="card-body">

            <form
                method="GET"
                action="{{ route('schedule-monitor.history') }}"
            >

                <div class="row g-3">

                    {{-- Search --}}

                    <div class="col-md-4">

                        <label class="form-label fw-semibold">
                            Search
                        </label>

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            value="{{ $search }}"
                            placeholder="Task name, type or status..."
                        >

                    </div>


                    {{-- Status --}}

                    <div class="col-md-2">

                        <label class="form-label fw-semibold">
                            Status
                        </label>

                        <select
                            name="status"
                            class="form-select"
                        >

                            <option value="">
                                All Statuses
                            </option>

                            <option
                                value="starting"
                                {{ $status === 'starting'
                                    ? 'selected'
                                    : '' }}
                            >
                                Started
                            </option>

                            <option
                                value="finished"
                                {{ $status === 'finished'
                                    ? 'selected'
                                    : '' }}
                            >
                                Finished
                            </option>

                            <option
                                value="failed"
                                {{ $status === 'failed'
                                    ? 'selected'
                                    : '' }}
                            >
                                Failed
                            </option>

                            <option
                                value="skipped"
                                {{ $status === 'skipped'
                                    ? 'selected'
                                    : '' }}
                            >
                                Skipped
                            </option>

                        </select>

                    </div>


                    {{-- Task Type --}}

                    <div class="col-md-2">

                        <label class="form-label fw-semibold">
                            Task Type
                        </label>

                        <select
                            name="task_type"
                            class="form-select"
                        >

                            <option value="">
                                All Types
                            </option>

                            @foreach ($taskTypes as $type)

                                <option
                                    value="{{ $type }}"
                                    {{ $taskType === $type
                                        ? 'selected'
                                        : '' }}
                                >
                                    {{ $type }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- From Date --}}

                    <div class="col-md-2">

                        <label class="form-label fw-semibold">
                            From Date
                        </label>

                        <input
                            type="date"
                            name="date_from"
                            class="form-control"
                            value="{{ $dateFrom }}"
                        >

                    </div>


                    {{-- To Date --}}

                    <div class="col-md-2">

                        <label class="form-label fw-semibold">
                            To Date
                        </label>

                        <input
                            type="date"
                            name="date_to"
                            class="form-control"
                            value="{{ $dateTo }}"
                        >

                    </div>


                    {{-- Sort --}}

                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Date Sorting
                        </label>

                        <select
                            name="sort"
                            class="form-select"
                        >

                            <option
                                value="desc"
                                {{ $sort === 'desc'
                                    ? 'selected'
                                    : '' }}
                            >
                                Newest First
                            </option>

                            <option
                                value="asc"
                                {{ $sort === 'asc'
                                    ? 'selected'
                                    : '' }}
                            >
                                Oldest First
                            </option>

                        </select>

                    </div>


                    {{-- Records Per Page --}}

                    <div class="col-md-3">

                        <label class="form-label fw-semibold">
                            Records Per Page
                        </label>

                        <select
                            name="per_page"
                            class="form-select"
                        >

                            @foreach ([5, 10, 25, 50] as $number)

                                <option
                                    value="{{ $number }}"
                                    {{ $perPage == $number
                                        ? 'selected'
                                        : '' }}
                                >
                                    {{ $number }} Records
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Buttons --}}

                    <div class="col-md-6
                                d-flex align-items-end
                                gap-2">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            🔎 Apply Filters
                        </button>

                        <a
                            href="{{ route(
                                'schedule-monitor.history'
                            ) }}"
                            class="btn btn-outline-secondary"
                        >
                            Reset
                        </a>

                    </div>

                </div>

            </form>


            {{-- Quick Filters --}}

            <div class="border-top mt-4 pt-3">

                <div class="fw-semibold mb-2">
                    Quick Date Filters
                </div>

                <div class="d-flex flex-wrap gap-2">

                    <a
                        href="{{ route(
                            'schedule-monitor.history',
                            array_merge(
                                request()->except('page'),
                                [
                                    'quick_filter' => 'today'
                                ]
                            )
                        ) }}"
                        class="btn btn-outline-primary btn-sm"
                    >
                        📅 Today
                    </a>


                    <a
                        href="{{ route(
                            'schedule-monitor.history',
                            array_merge(
                                request()->except('page'),
                                [
                                    'quick_filter' => 'this_month'
                                ]
                            )
                        ) }}"
                        class="btn btn-outline-primary btn-sm"
                    >
                        📅 This Month
                    </a>


                    <a
                        href="{{ route(
                            'schedule-monitor.history',
                            array_merge(
                                request()->except('page'),
                                [
                                    'quick_filter' => 'last_7_days'
                                ]
                            )
                        ) }}"
                        class="btn btn-outline-primary btn-sm"
                    >
                        📅 Last 7 Days
                    </a>

                </div>

            </div>

        </div>

    </div>


    {{-- Filter Result Summary --}}

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <div class="row text-center">

                <div class="col-md-4">

                    <div class="text-muted small">
                        Filtered Records
                    </div>

                    <div class="fs-3 fw-bold text-primary">
                        {{ number_format($filteredCount) }}
                    </div>

                </div>


                <div class="col-md-4">

                    <div class="text-muted small">
                        Current Page
                    </div>

                    <div class="fs-3 fw-bold">
                        {{ $logs->count() }}
                    </div>

                </div>


                <div class="col-md-4">

                    <div class="text-muted small">
                        Current Page Number
                    </div>

                    <div class="fs-3 fw-bold">
                        {{ $logs->currentPage() }}
                    </div>

                </div>

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

                <table
                    class="table table-hover
                           align-middle mb-0"
                >

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

                                    {{ $log->name
                                        ?: 'Unnamed Task' }}

                                </td>


                                <td>

                                    {{ $log->task_type
                                        ?: 'N/A' }}

                                </td>


                                <td>

                                    @if (
                                        $log->type === 'finished'
                                    )

                                        <span
                                            class="badge bg-success"
                                        >
                                            Finished
                                        </span>

                                    @elseif (
                                        $log->type === 'failed'
                                    )

                                        <span
                                            class="badge bg-danger"
                                        >
                                            Failed
                                        </span>

                                    @elseif (
                                        $log->type === 'skipped'
                                    )

                                        <span
                                            class="badge
                                                   bg-warning
                                                   text-dark"
                                        >
                                            Skipped
                                        </span>

                                    @elseif (
                                        $log->type === 'starting'
                                    )

                                        <span
                                            class="badge bg-primary"
                                        >
                                            Started
                                        </span>

                                    @else

                                        <span
                                            class="badge bg-secondary"
                                        >
                                            {{ ucfirst(
                                                $log->type
                                            ) }}
                                        </span>

                                    @endif

                                </td>


                                <td>

                                    <code>
                                        {{ $log->cron_expression }}
                                    </code>

                                </td>


                                <td>

                                    {{ \Carbon\Carbon::parse(
                                        $log->created_at
                                    )->format(
                                        'Y-m-d H:i:s'
                                    ) }}

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="6"
                                    class="text-center
                                           text-muted
                                           py-5"
                                >

                                    No execution history
                                    found for the selected
                                    filters.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- Numeric Pagination Only --}}

        @if ($logs->hasPages())

            <div class="card-footer bg-white">

                <div
                    class="d-flex
                           justify-content-center"
                >

                    {{ $logs->onEachSide(1)
                        ->links(
                            'pagination::bootstrap-5'
                        ) }}

                </div>

            </div>

        @endif

    </div>

</div>

</body>

</html>