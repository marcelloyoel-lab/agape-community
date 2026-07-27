@extends('layouts.main.app')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Weekly Schedules</h2>
                <p class="text-muted mb-0">
                    Manage weekly service schedules and ministry assignments.
                </p>
            </div>

            <a href="{{ route('schedules.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>
                Create Schedule
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">

                <div class="table-responsive">
                    <table id="schedules-table"
                           class="table table-hover align-middle w-100">

                        <thead>
                            <tr>
                                <th>Service Date</th>
                                <th>Status</th>
                                <th>Assignments</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($schedules as $schedule)
                                <tr>
                                    <td>
                                        {{ $schedule->service_date->format('d M Y') }}
                                    </td>

                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $schedule->status->label() }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $schedule->assignments_count }}
                                    </td>

                                    <td>
                                        {{ $schedule->creator->name }}
                                    </td>

                                    <td>
                                        {{ $schedule->created_at->format('d M Y H:i') }}
                                    </td>

                                    <td class="text-center">
                                        <a href="{{ route('schedules.show', $schedule) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>
@endsection