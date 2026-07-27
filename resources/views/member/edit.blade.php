@extends('layouts.main.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Edit Member</h4>

        <a href="{{ route('members.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <form
                action="{{ route('members.update', $member) }}"
                method="POST"
            >
                @csrf
                @method('PUT')

                <div class="row">

                    {{-- Name --}}
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">
                            Name <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $member->name) }}"
                            autofocus
                        >

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Gender --}}
                    <div class="col-md-6 mb-3">
                        <label for="gender" class="form-label">
                            Gender <span class="text-danger">*</span>
                        </label>

                        <select
                            id="gender"
                            name="gender"
                            class="form-select @error('gender') is-invalid @enderror"
                        >
                            <option value="">-- Select Gender --</option>

                            @foreach (\App\Enums\Gender::cases() as $gender)
                                <option
                                    value="{{ $gender->value }}"
                                    @selected(
                                        old('gender', $member->gender?->value) === $gender->value
                                    )
                                >
                                    {{ $gender->label() }}
                                </option>
                            @endforeach
                        </select>

                        @error('gender')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Phone Number --}}
                    <div class="col-md-6 mb-3">
                        <label for="phone_number" class="form-label">
                            Phone Number
                        </label>

                        <input
                            type="text"
                            id="phone_number"
                            name="phone_number"
                            class="form-control @error('phone_number') is-invalid @enderror"
                            value="{{ old('phone_number', $member->phone_number) }}"
                        >

                        @error('phone_number')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Status --}}
                    <div class="col-md-6 mb-3">
                        <label for="is_active" class="form-label">
                            Status <span class="text-danger">*</span>
                        </label>

                        <select
                            id="is_active"
                            name="is_active"
                            class="form-select @error('is_active') is-invalid @enderror"
                        >
                            <option
                                value="1"
                                @selected((string) old('is_active', (int) $member->is_active) === '1')
                            >
                                Active
                            </option>

                            <option
                                value="0"
                                @selected((string) old('is_active', (int) $member->is_active) === '0')
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

                </div>

                <div class="d-flex justify-content-end gap-2 mt-2">
                    <a
                        href="{{ route('members.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-primary">
                        Update Member
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
@endsection