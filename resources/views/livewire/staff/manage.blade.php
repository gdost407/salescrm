<?php

use Livewire\Volt\Component;

new class extends Component {
    public $staffMembers = [];
    public $searchTerm = '';
    public $filterDepartment = '';

    public function mount()
    {
        $this->loadStaff();
    }

    public function loadStaff()
    {
        // Sample data - replace with actual database query
        $this->staffMembers = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'department' => 'Sales', 'role' => 'Sales Executive', 'status' => 'Active'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'department' => 'Sales', 'role' => 'Sales Manager', 'status' => 'Active'],
            ['id' => 3, 'name' => 'Mike Johnson', 'email' => 'mike@example.com', 'department' => 'Marketing', 'role' => 'Team Lead', 'status' => 'Active'],
            ['id' => 4, 'name' => 'Sarah Williams', 'email' => 'sarah@example.com', 'department' => 'Operations', 'role' => 'Administrator', 'status' => 'Inactive'],
        ];
    }
}; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold py-3 mb-0">Manage Staff</h4>
        </div>
        <div>
            <a href="{{ route('staff-create') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Add Staff Member
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search staff..." wire:model="searchTerm">
                    </div>
                </div>
                <div class="col-md-6">
                    <select class="form-select" wire:model="filterDepartment">
                        <option value="">All Departments</option>
                        <option value="Sales">Sales</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Operations">Operations</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($staffMembers as $staff)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <span class="avatar-initial rounded-circle bg-primary">{{ substr($staff['name'], 0, 1) }}</span>
                                    </div>
                                    <strong>{{ $staff['name'] }}</strong>
                                </div>
                            </td>
                            <td>{{ $staff['email'] }}</td>
                            <td>{{ $staff['department'] }}</td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $staff['role'] }}</span>
                            </td>
                            <td>
                                @if ($staff['status'] === 'Active')
                                    <span class="badge bg-success">{{ $staff['status'] }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $staff['status'] }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon btn-text-secondary" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">
                                            <i class="bx bx-edit-alt"></i> Edit
                                        </a>
                                        <a class="dropdown-item" href="#">
                                            <i class="bx bx-show"></i> View Details
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="#">
                                            <i class="bx bx-check"></i> Toggle Status
                                        </a>
                                        <a class="dropdown-item text-danger" href="#">
                                            <i class="bx bx-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No staff members found. <a href="{{ route('staff-create') }}">Add one now</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Staff Statistics -->
    <div class="row mt-4">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title small text-muted">Total Staff</h6>
                    <h4 class="card-text mb-0">{{ count($staffMembers) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title small text-muted">Active</h6>
                    <h4 class="card-text mb-0 text-success">{{ count(array_filter($staffMembers, fn($s) => $s['status'] === 'Active')) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title small text-muted">Inactive</h6>
                    <h4 class="card-text mb-0 text-danger">{{ count(array_filter($staffMembers, fn($s) => $s['status'] === 'Inactive')) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <h6 class="card-title small text-muted">Departments</h6>
                    <h4 class="card-text mb-0">{{ count(array_unique(array_column($staffMembers, 'department'))) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>

