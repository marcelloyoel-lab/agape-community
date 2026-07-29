@extends('layouts.main.app')

@section('content')
@vite(['resources/assets/css/schedule/create.css'])
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Edit Weekly Schedule</h2>
                <p class="text-muted mb-0">
                    Update the service information and ministry assignments.
                </p>
            </div>

            <a href="{{ route('schedules.index') }}"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back
            </a>
        </div>

        <form action="{{ route('schedules.update', $schedule) }}"
              method="POST"
              id="schedule-form">

            @csrf
            @method('PUT')

            {{-- Service Information --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Service Information</h5>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="service_date" class="form-label">
                                Service Date
                            </label>

                            <input
                                type="date"
                                name="service_date"
                                id="service_date"
                                class="form-control @error('service_date') is-invalid @enderror"
                                value="{{ old('service_date', $schedule->service_date->format('Y-m-d')) }}"
                                required
                            >

                            @error('service_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="service_time" class="form-label">
                                Service Time
                            </label>

                            <input
                                type="time"
                                name="service_time"
                                id="service_time"
                                class="form-control @error('service_time') is-invalid @enderror"
                                value="{{ old('service_time', substr($schedule->service_time, 0, 5)) }}"
                                required
                            >

                            @error('service_time')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- Ministry Assignments --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ministry Assignments</h5>
                </div>

                <div class="card-body">
                    <div class="row g-4">

                        @foreach ($ministries as $ministry)
                            @php
                                $selectedMembers = old(
                                    "assignments.{$ministry->id}",
                                    $selectedAssignments->get($ministry->id, [])
                                );
                            @endphp

                            <div class="col-lg-6">
                                <label
                                    for="ministry-{{ $ministry->id }}"
                                    class="form-label fw-semibold"
                                >
                                    {{ $ministry->name }}

                                    @if ($ministry->allow_multiple_members)
                                        <span class="text-muted fw-normal">
                                            (Multiple)
                                        </span>
                                    @endif
                                </label>

                                <select
                                    id="ministry-{{ $ministry->id }}"
                                    name="assignments[{{ $ministry->id }}][]"
                                    class="form-select member-select
                                        @error("assignments.{$ministry->id}") is-invalid @enderror"
                                    data-placeholder="Select member{{ $ministry->allow_multiple_members ? 's' : '' }}"
                                    @if ($ministry->allow_multiple_members) multiple @endif
                                    required
                                >
                                    @unless ($ministry->allow_multiple_members)
                                        <option></option>
                                    @endunless

                                    @foreach ($members as $member)
                                        <option
                                            value="{{ $member->id }}"
                                            @selected(
                                                in_array(
                                                    $member->id,
                                                    $selectedMembers
                                                )
                                            )
                                        >
                                            {{ $member->name }}
                                        </option>
                                    @endforeach
                                </select>

                                @if ($ministry->allow_multiple_members)
                                    <div class="form-text">
                                        You can select more than one member.
                                    </div>
                                @endif

                                @error("assignments.{$ministry->id}")
                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror

                                @error("assignments.{$ministry->id}.*")
                                    <div class="text-danger small mt-1">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @endforeach

                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('schedules.index') }}"
                   class="btn btn-outline-secondary">
                    Cancel
                </a>

                <button type="submit"
                        class="btn btn-primary"
                        id="submit-button">
                    <i class="bi bi-check-lg me-1"></i>
                    Update Schedule
                </button>
            </div>

        </form>

    </div>
@endsection
@vite(['resources/assets/js/schedule/edit.js'])