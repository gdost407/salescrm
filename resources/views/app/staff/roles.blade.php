@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-3 mb-4">Roles & Permissions</h4>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">CRM Roles</h5>
                </div>
                <div class="card-body">
                    @if (session('message'))
                        <div class="alert alert-success">{{ session('message') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                    <h6>{{ $editingRole ? 'Edit role' : 'Create role' }}</h6>
                    <form action="{{ $editingRole ? route('staff.roles.update', $editingRole) : route('staff.roles.store') }}" method="POST" class="mb-4">
                        @csrf
                        @if ($editingRole)
                            @method('PUT')
                        @endif
                        <input type="hidden" name="permissions_submitted" value="1">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label" for="role_name">Role name</label>
                                <input type="text" name="name" id="role_name" class="form-control" placeholder="Sales Staff" value="{{ old('name', $editingRole?->name) }}" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="role_slug">Slug</label>
                                <input type="text" name="slug" id="role_slug" class="form-control" placeholder="sales-staff" value="{{ old('slug', $editingRole?->slug) }}" @readonly($editingRole)>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="role_description">Description</label>
                                <textarea name="description" id="role_description" class="form-control" rows="2" placeholder="Only assigned leads and export access">{{ old('description', $editingRole?->description) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Permissions</label>
                                @foreach ($permissions->groupBy('module')->sortBy(fn ($items, $module) => array_search($module, ['lead', 'staff', 'report', 'admin'], true)) as $module => $modulePermissions)
                                <fieldset class="border rounded p-3 mb-3">
                                    <legend class="float-none w-auto px-2 fs-6">{{ match ($module) { 'lead' => 'Lead permissions', 'staff' => 'Staff permissions', 'report' => 'Reports', 'admin' => 'Administration', default => ucfirst($module ?: 'Other') } }}</legend>
                                    <div class="row g-3">
                                    @foreach ($modulePermissions as $permission)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->slug }}" id="perm-{{ $permission->id }}" @checked(in_array($permission->slug, old('permissions_submitted') ? old('permissions', []) : ($editingRole?->permissions->pluck('slug')->all() ?? []), true))>
                                                <label class="form-check-label" for="perm-{{ $permission->id }}">{{ $permission->name }}</label>
                                                @if ($permission->slug === 'manage_team')
                                                    <small class="text-muted d-block">Includes view, create, edit, and password actions. Role management is separate.</small>
                                                @elseif ($permission->slug === 'manage_roles')
                                                    <small class="text-muted d-block">Create roles, change permissions, and assign access roles to staff.</small>
                                                @elseif ($permission->slug === 'admin_access')
                                                    <small class="text-muted d-block">Grants every permission, including staff and role management.</small>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                    </div>
                                </fieldset>
                                @endforeach
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary w-100">{{ $editingRole ? 'Update Role' : 'Create Role' }}</button>
                                @if ($editingRole)
                                    <a href="{{ route('staff-roles') }}" class="btn btn-secondary mt-2">Cancel editing</a>
                                @endif
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
                                        <a href="{{ route('staff.roles.edit', $role) }}">Edit permissions</a>
                                        @if ($role->description)
                                            <small class="text-muted">{{ $role->description }}</small>
                                        @endif
                                    </div>
                                    <span class="badge {{ $role->permissions->contains('slug', 'admin_access') ? 'bg-danger' : 'bg-primary' }} rounded-pill">{{ $role->permissions->contains('slug', 'admin_access') ? 'Full access' : 'Custom access' }}</span>
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

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Permission Reference</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <h6>Leads</h6>
                            <ul class="list-unstyled small mb-0">
                                <li><strong>View Leads:</strong> Access to view leads</li>
                                <li><strong>Create Leads:</strong> Add new leads</li>
                                <li><strong>Edit Own Leads:</strong> Edit only assigned leads</li>
                                <li><strong>Edit All Leads:</strong> Edit any lead</li>
                                <li><strong>Delete Leads:</strong> Remove leads</li>
                                <li><strong>Export Leads:</strong> Download CSV / export</li>
                                <li><strong>Print Leads:</strong> Print lead list or details</li>
                            </ul>
                        </div>
                        <div class="col-12">
                            <h6>Staff</h6>
                            <ul class="list-unstyled small mb-0">
                                <li><strong>View Staff:</strong> Open the staff list</li>
                                <li><strong>Create Staff:</strong> Add staff accounts</li>
                                <li><strong>Edit Staff:</strong> Update staff details and active status</li>
                                <li><strong>Resend Staff Password:</strong> Generate and email a new password</li>
                                <li><strong>Manage Team:</strong> Includes view, create, edit, and password actions</li>
                                <li><strong>Manage Roles &amp; Permissions:</strong> Configure and assign access roles; also required to manage administrator accounts</li>
                            </ul>
                        </div>
                        <div class="col-12">
                            <h6>Reports &amp; Administration</h6>
                            <ul class="list-unstyled small mb-0">
                                <li><strong>View Reports:</strong> Access dashboard reporting</li>
                                <li><strong>Admin Access:</strong> Grants all lead, staff, and role permissions</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
