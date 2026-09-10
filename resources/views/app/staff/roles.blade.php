@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-3 mb-4">Roles & Permissions</h4>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">CRM Roles</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('staff.roles.store') }}" method="POST" class="mb-4">
                        @csrf
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label" for="role_name">Role name</label>
                                <input type="text" name="name" id="role_name" class="form-control" placeholder="Sales Staff" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="role_slug">Slug</label>
                                <input type="text" name="slug" id="role_slug" class="form-control" placeholder="sales-staff">
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="role_description">Description</label>
                                <textarea name="description" id="role_description" class="form-control" rows="2" placeholder="Only assigned leads and export access"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Permissions</label>
                                <div class="row g-2">
                                    @foreach ($permissions as $permission)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->slug }}" id="perm-{{ $permission->id }}">
                                                <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-plus"></i> Save Role</button>
                            </div>
                        </div>
                    </form>

                    <div class="list-group">
                        @forelse ($roles as $role)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div>
                                        <h6 class="mb-1">{{ $role->name }}</h6>
                                        <small class="text-muted d-block">{{ $role->slug }}</small>
                                        @if ($role->description)
                                            <small class="text-muted">{{ $role->description }}</small>
                                        @endif
                                    </div>
                                    <span class="badge {{ $role->slug === 'admin' ? 'bg-danger' : 'bg-primary' }} rounded-pill">{{ $role->slug === 'admin' ? 'Admin' : 'Staff' }}</span>
                                </div>
                                @if ($role->permissions->isNotEmpty())
                                    <div class="mt-2 d-flex flex-wrap gap-1">
                                        @foreach ($role->permissions as $permission)
                                            <span class="badge bg-light text-dark">{{ $permission->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="alert alert-info mb-0">No roles created yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Permission Reference</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <ul class="list-unstyled small mb-0">
                                <li><strong>View Leads:</strong> Access to view leads</li>
                                <li><strong>Create Leads:</strong> Add new leads</li>
                                <li><strong>Edit Own Leads:</strong> Edit only assigned leads</li>
                                <li><strong>Edit All Leads:</strong> Edit any lead</li>
                                <li><strong>Delete Leads:</strong> Remove leads</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled small mb-0">
                                <li><strong>Export Leads:</strong> Download CSV / export</li>
                                <li><strong>Print Leads:</strong> Print lead list or details</li>
                                <li><strong>View Reports:</strong> Access dashboard reporting</li>
                                <li><strong>Manage Team:</strong> Control staff members</li>
                                <li><strong>Admin Access:</strong> Full CRM access including all leads</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
