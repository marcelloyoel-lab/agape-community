@extends('layouts.main.app')

@section('content')
@vite('resources/assets/js/member/index.js')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Member Management</h4>

        <a href="{{ route('members.create') }}" class="btn btn-primary">
            <i class="bx bx-plus"></i>
            Add Member
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover" id="memberTable">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th width="20%">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($members as $member)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>{{ $member->name }}</td>

                                <td>
                                    @if ($member->is_active)
                                        <span class="badge bg-label-success">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-label-danger">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <a
                                        href="{{ route('members.edit', $member) }}"
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </a>

                                    @if ($member->is_active)
                                        <button
                                            class="btn btn-sm btn-danger"
                                        >
                                            Deactivate
                                        </button>
                                    @else
                                        <button
                                            class="btn btn-sm btn-success"
                                        >
                                            Activate
                                        </button>
                                    @endif
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

@vite('resources/js/member/index.js')