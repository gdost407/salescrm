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
                                <textarea name="description" id="role_description" class="form-control" rows="2" placeholder="Describe access within these modules">{{ old('description', $editingRole?->description) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Permissions</label>
                                @foreach ($permissions->groupBy('module') as $module => $modulePermissions)
                                <fieldset class="border rounded p-3 mb-3">
                                    <legend class="float-none w-auto px-2 fs-6">{{ str($module)->replace('_', ' ')->ucfirst() }} permissions</legend>
                                    <div class="row g-3">
                                    @foreach ($modulePermissions as $permission)
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->slug }}" id="perm-{{ $permission->id }}" @checked(in_array($permission->slug, old('permissions_submitted') ? old('permissions', []) : ($editingRole?->permissions->pluck('slug')->all() ?? []), true))>
                                                <label class="form-check-label" for="perm-{{ $permission->id }}">{{ \App\Models\Permission::MODULES[$module][$permission->slug] }}</label>

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
                                    <span class="badge bg-primary rounded-pill">Module access</span>
                                </div>
                                @if ($role->permissions->isNotEmpty())
                                    <div class="mt-2 d-flex flex-wrap gap-1">
                                        @foreach ($role->permissions as $permission)
                                            <span class="badge bg-light text-dark">{{ ucfirst($permission->module) }}: {{ \App\Models\Permission::MODULES[$permission->module][$permission->slug] }}</span>
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
                    <p>Lead permissions retain create and self/all view, update, and delete access. Staff has only Create, View, Edit, and Delete within your company.</p>
                    <p>Lead Activity permissions control adding, updating, and deleting activities, scheduling and managing follow-ups, and working/completing them. Follow-up permissions also cover scheduled visits and meetings.</p>
                    <p>Self/assigned means activities on assigned leads or activities you created. All means company records only. Lead view access is required.</p>
                    <p>Calendar, Kanban, lists, export, and print follow Lead view permissions. Lead settings require Lead Edit all.</p>
                    <p>Staff Edit controls role configuration and assignment. Staff can assign only permissions they already hold and cannot change their own access role.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
