@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-3 mb-4">Roles & Permissions</h4>
    </div>

    <div class="row">
        <!-- Roles List -->
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Roles</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="input-group">
                            <input type="text" class="form-control" id="newRole" placeholder="New role name">
                            <button class="btn btn-outline-primary" type="button">
                                <i class="bx bx-plus"></i> Add
                            </button>
                        </div>
                    </div>

                    <div class="list-group">
                        @foreach ($roles as $role)
                            <button type="button" class="list-group-item list-group-item-action @if(isset($_GET['role_id']) && $_GET['role_id'] == $role['id']) active @endif">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1">{{ $role['name'] }}</h6>
                                        <small>{{ $role['description'] }}</small>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Permissions -->
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Select a role to manage permissions</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-0">
                        <i class="bx bx-info-circle"></i> Click on a role from the left panel to manage its permissions.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permission Legend -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title mb-3">Permission Reference</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled small">
                                <li><strong>View Leads:</strong> Access to view all leads</li>
                                <li><strong>Create Leads:</strong> Can create new leads</li>
                                <li><strong>Edit Own Leads:</strong> Can edit only their own leads</li>
                                <li><strong>Edit Team Leads:</strong> Can edit team member leads</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled small">
                                <li><strong>Edit All Leads:</strong> Can edit any lead</li>
                                <li><strong>Delete Leads:</strong> Can delete leads</li>
                                <li><strong>View Reports:</strong> Access to analytics and reports</li>
                                <li><strong>Manage Team:</strong> Can manage team members</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
