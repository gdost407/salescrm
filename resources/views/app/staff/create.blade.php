@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-3 mb-4">Create New Staff Member</h4>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('staff-create') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">First Name *</label>
                                <input type="text" class="form-control @error('firstName') is-invalid @enderror" id="firstName" name="firstName" placeholder="John" value="{{ old('firstName') }}">
                                @error('firstName') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Last Name *</label>
                                <input type="text" class="form-control @error('lastName') is-invalid @enderror" id="lastName" name="lastName" placeholder="Doe" value="{{ old('lastName') }}">
                                @error('lastName') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" placeholder="john.doe@example.com" value="{{ old('email') }}">
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="+1 (555) 000-0000" value="{{ old('phone') }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="department" class="form-label">Department *</label>
                                <input type="text" class="form-control @error('department') is-invalid @enderror" id="department" name="department" placeholder="Sales, Marketing, etc." value="{{ old('department') }}">
                                @error('department') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">Role *</label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role">
                                    <option value="">Select Role</option>
                                    <option value="Sales Executive" {{ old('role') === 'Sales Executive' ? 'selected' : '' }}>Sales Executive</option>
                                    <option value="Sales Manager" {{ old('role') === 'Sales Manager' ? 'selected' : '' }}>Sales Manager</option>
                                    <option value="Team Lead" {{ old('role') === 'Team Lead' ? 'selected' : '' }}>Team Lead</option>
                                    <option value="Administrator" {{ old('role') === 'Administrator' ? 'selected' : '' }}>Administrator</option>
                                </select>
                                @error('role') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date *</label>
                            <input type="date" class="form-control @error('startDate') is-invalid @enderror" id="startDate" name="startDate" value="{{ old('startDate') }}">
                            @error('startDate') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> Create Staff
                            </button>
                            <a href="{{ route('staff-manage') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title mb-3">Staff Registration Tips</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="bx bx-check text-success"></i> Email will be used for login
                        </li>
                        <li class="mb-2">
                            <i class="bx bx-check text-success"></i> Ensure unique email addresses
                        </li>
                        <li class="mb-2">
                            <i class="bx bx-check text-success"></i> Select appropriate role
                        </li>
                        <li class="mb-2">
                            <i class="bx bx-check text-success"></i> Department helps in organization
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
