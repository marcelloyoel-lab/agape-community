@extends('layouts.main.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Member Management</h4>

        <a href="{{ route('members.create') }}" class="btn btn-primary">
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
                                        <span class="badge bg-success">
                                            Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="d-flex gap-2">

                                        <a
                                            href="{{ route('members.edit', $member) }}"
                                            class="btn btn-sm btn-warning"
                                        >
                                            Edit
                                        </a>

                                        <button
                                            type="button"
                                            class="btn btn-sm {{ $member->is_active ? 'btn-danger' : 'btn-success' }} js-status-button"
                                            data-bs-toggle="modal"
                                            data-bs-target="#statusModal"
                                            data-member-name="{{ $member->name }}"
                                            data-status="{{ $member->is_active ? '0' : '1' }}"
                                            data-action="{{ route('members.update-status', $member) }}"
                                        >
                                            {{ $member->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>


{{-- Status Confirmation Modal --}}
<div
    class="modal fade"
    id="statusModal"
    tabindex="-1"
    aria-labelledby="statusModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="statusModalLabel">
                    Confirm Status Change
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>

            <div class="modal-body">
                <p class="mb-0" id="statusModalMessage"></p>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Cancel
                </button>

                <form id="statusForm" method="POST">
                    @csrf
                    @method('PATCH')

                    <input
                        type="hidden"
                        name="is_active"
                        id="statusInput"
                    >

                    <button
                        type="submit"
                        class="btn"
                        id="statusConfirmButton"
                    >
                        Confirm
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection

@vite('resources/assets/js/member/index.js')