@extends('layouts.main.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Create Member</h4>

        <a href="{{ route('members.index') }}" class="btn btn-secondary">
            Back
        </a>
    </div>

    <div class="card">
        <div class="card-body">

            <form action="{{ route('members.store') }}" method="POST">

                @csrf

                <div class="row">

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Name <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            autofocus
                        >

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Gender <span class="text-danger">*</span>
                        </label>

                        <select
                            name="gender"
                            class="form-select @error('gender') is-invalid @enderror"
                        >
                            <option value="">-- Select Gender --</option>

                            @foreach(\App\Enums\Gender::cases() as $gender)
                                <option
                                    value="{{ $gender->value }}"
                                    @selected(old('gender') == $gender->value)
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

                </div>

                <div class="row mb-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Phone Number
                        </label>
    
                        <input
                            type="text"
                            name="phone_number"
                            class="form-control @error('phone_number') is-invalid @enderror"
                            value="{{ old('phone_number') }}"
                        >
    
                        @error('phone_number')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">
                            Status <span class="text-danger">*</span>
                        </label>

                        <select
                            name="is_active"
                            class="form-select @error('is_active') is-invalid @enderror"
                        >
                            <option value="1" @selected(old('is_active', '1') == '1')>
                                Active
                            </option>

                            <option value="0" @selected(old('is_active') == '0')>
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

                <div class="d-flex justify-content-end gap-2">

                    <a
                        href="{{ route('members.index') }}"
                        class="btn btn-outline-secondary"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save
                    </button>

                </div>

            </form>

        </div>
    </div>

</div>
@endsection

@vite('resources/assets/js/member/create.js')