@extends('layouts.main.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Ministries</h1>
            <p class="text-muted mb-0">
                Manage ministries used for weekly schedules.
            </p>
        </div>

        <a href="{{ route('ministries.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>
            Add Ministry
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table
                    id="ministryTable"
                    class="table table-hover align-middle"
                >
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Display Order</th>
                            <th>Member Selection</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($ministries as $ministry)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>{{ $ministry->name }}</td>

                                <td>{{ $ministry->display_order }}</td>

                                <td>
                                    @if ($ministry->allow_multiple_members)
                                        <span class="badge text-bg-info">
                                            Multiple
                                        </span>
                                    @else
                                        <span class="badge text-bg-primary">
                                            Single
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($ministry->is_active)
                                        <span class="badge text-bg-success">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge text-bg-danger">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('ministries.edit', $ministry) }}"
                                    class="btn btn-sm btn-warning disabled">
                                        <i class="bi bi-pencil"></i>
                                        Edit
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