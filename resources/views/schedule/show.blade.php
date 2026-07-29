@extends('layouts.main.app')

@section('content')
    <div class="container-fluid">

        {{-- Header --}}
        <div class="d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="mb-1">Schedule Details</h2>
                <p class="text-muted mb-0">
                    View service information and ministry assignments.
                </p>
            </div>

            <div class="d-flex gap-2">
                @if (in_array($schedule->status, [
                    \App\Enums\ScheduleStatus::DRAFT,
                    \App\Enums\ScheduleStatus::REJECTED,
                ], true))
                    <a href="{{ route('schedules.edit', $schedule) }}"
                       class="btn btn-warning">
                        <i class="bi bi-pencil me-1"></i>
                        Edit
                    </a>
                @endif

                <a href="{{ route('schedules.index') }}"
                   class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back
                </a>
            </div>
        </div>

        {{-- Service Information --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Service Information</h5>
            </div>

            <div class="card-body">
                <div class="row g-4">

                    <div class="col-md-3">
                        <div class="text-muted small mb-1">
                            Service Date
                        </div>

                        <div class="fw-semibold">
                            {{ $schedule->service_date->format('d M Y') }}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted small mb-1">
                            Service Time
                        </div>

                        <div class="fw-semibold">
                            {{ \Carbon\Carbon::parse($schedule->service_time)->format('H:i') }}
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted small mb-1">
                            Status
                        </div>

                        <span class="badge bg-secondary">
                            {{ $schedule->status->label() }}
                        </span>
                    </div>

                    <div class="col-md-3">
                        <div class="text-muted small mb-1">
                            Created By
                        </div>

                        <div class="fw-semibold">
                            {{ $schedule->creator->name }}
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Ministry Assignments --}}
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">Ministry Assignments</h5>
            </div>

            <div class="card-body">

                @forelse ($groupedAssignments as $assignments)
                    @php
                        $ministry = $assignments->first()->ministry;
                    @endphp

                    <div class="row py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="col-md-4">
                            <div class="fw-semibold">
                                {{ $ministry->name }}
                            </div>

                            @if ($ministry->allow_multiple_members)
                                <small class="text-muted">
                                    Multiple members
                                </small>
                            @endif
                        </div>

                        <div class="col-md-8">
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($assignments->sortBy('display_order') as $assignment)
                                    <span class="badge text-bg-light border">
                                        {{ $assignment->member->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>

                @empty
                    <div class="text-center text-muted py-4">
                        No ministry assignments found.
                    </div>
                @endforelse

            </div>
        </div>

        <form
            action="{{ route('schedules.poster.generate', $schedule) }}"
            method="POST"
            class="d-block mt-4"
            id="generatePosterForm"
        >
            @csrf

            <button
                type="submit"
                class="btn btn-primary"
            >
                Generate & Download Poster
            </button>
        </form>

    </div>
@endsection