@extends('layouts.main.app')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1 class="h3 mb-1">Edit Ministry</h1>
        <p class="text-muted mb-0">
            Update ministry information.
        </p>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">

            <form
                action="{{ route('ministries.update', $ministry) }}"
                method="POST"
                id="ministryForm"
            >
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label">
                        Ministry Name
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $ministry->name) }}"
                        class="form-control @error('name') is-invalid @enderror"
                        maxlength="100"
                        required
                    >

                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label
                        for="allow_multiple_members"
                        class="form-label"
                    >
                        Member Assignment
                    </label>

                    <select
                        id="allow_multiple_members"
                        name="allow_multiple_members"
                        class="form-select @error('allow_multiple_members') is-invalid @enderror"
                        required
                    >
                        <option value="">Select assignment type</option>

                        <option
                            value="0"
                            @selected(
                                (string) old(
                                    'allow_multiple_members',
                                    (int) $ministry->allow_multiple_members
                                ) === '0'
                            )
                        >
                            Single Member
                        </option>

                        <option
                            value="1"
                            @selected(
                                (string) old(
                                    'allow_multiple_members',
                                    (int) $ministry->allow_multiple_members
                                ) === '1'
                            )
                        >
                            Multiple Members
                        </option>
                    </select>

                    @error('allow_multiple_members')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="is_active" class="form-label">
                        Status
                    </label>

                    <select
                        id="is_active"
                        name="is_active"
                        class="form-select @error('is_active') is-invalid @enderror"
                        required
                    >
                        <option
                            value="1"
                            @selected(
                                (string) old(
                                    'is_active',
                                    (int) $ministry->is_active
                                ) === '1'
                            )
                        >
                            Active
                        </option>

                        <option
                            value="0"
                            @selected(
                                (string) old(
                                    'is_active',
                                    (int) $ministry->is_active
                                ) === '0'
                            )
                        >
                            Inactive
                        </option>
                    </select>

                    @error('is_active')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        Update Ministry
                    </button>

                    <a
                        href="{{ route('ministries.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Cancel
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection