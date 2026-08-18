<?php

use Livewire\Volt\Component;

new class extends Component {
    public $roles = [];
    public $newRoleName = '';
    public $permissions = [];
    public $selectedRoleId = null;

    public function mount()
    {
        $this->loadRoles();
    }

    public function loadRoles()
    {
        // Sample data - replace with actual database query
        $this->roles = [
            [
                'id' => 1,
                'name' => 'Sales Executive',
                'description' => 'Can manage own leads and activities',
                'permissions' => ['view_leads', 'create_leads', 'edit_own_leads'],
            ],
            [
                'id' => 2,
                'name' => 'Sales Manager',
                'description' => 'Can manage team leads and performance',
                'permissions' => ['view_leads', 'create_leads', 'edit_all_leads', 'view_reports', 'manage_team'],
            ],
            [
                'id' => 3,
                'name' => 'Team Lead',
                'description' => 'Can oversee team activities',
                'permissions' => ['view_leads', 'create_leads', 'edit_team_leads', 'view_reports'],
            ],
            [
                'id' => 4,
                'name' => 'Administrator',
                'description' => 'Full system access',
                'permissions' => ['all'],
            ],
        ];

        $this->permissions = [
            ['name' => 'view_leads', 'label' => 'View Leads'],
            ['name' => 'create_leads', 'label' => 'Create Leads'],
            ['name' => 'edit_own_leads', 'label' => 'Edit Own Leads'],
            ['name' => 'edit_team_leads', 'label' => 'Edit Team Leads'],
            ['name' => 'edit_all_leads', 'label' => 'Edit All Leads'],
            ['name' => 'delete_leads', 'label' => 'Delete Leads'],
            ['name' => 'view_reports', 'label' => 'View Reports'],
            ['name' => 'manage_team', 'label' => 'Manage Team'],
        ];
    }

    public function selectRole($roleId)
    {
        $this->selectedRoleId = $roleId;
    }

    public function addRole()
    {
        if ($this->newRoleName) {
            $this->roles[] = [
                'id' => count($this->roles) + 1,
                'name' => $this->newRoleName,
                'description' => '',
                'permissions' => [],
            ];
            $this->newRoleName = '';
        }
    }
}; ?>

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
                            <input type="text" class="form-control" id="newRole" wire:model="newRoleName" placeholder="New role name">
                            <button class="btn btn-outline-primary" type="button" wire:click="addRole">
                                <i class="bx bx-plus"></i> Add
                            </button>
                        </div>
                    </div>

                    <div class="list-group">
                        @foreach ($roles as $role)
                            <button type="button" class="list-group-item list-group-item-action {{ $selectedRoleId === $role['id'] ? 'active' : '' }}"
                                wire:click="selectRole({{ $role['id'] }})">
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
                    <h5 class="card-title mb-0">
                        @if ($selectedRoleId)
                            {{ collect($roles)->firstWhere('id', $selectedRoleId)['name'] ?? 'Select a Role' }} - Permissions
                        @else
                            Select a role to manage permissions
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @if ($selectedRoleId && collect($roles)->firstWhere('id', $selectedRoleId))
                        @php
                            $selectedRole = collect($roles)->firstWhere('id', $selectedRoleId);
                        @endphp

                        <div class="mb-3">
                            <label for="roleDesc" class="form-label">Role Description</label>
                            <textarea class="form-control" id="roleDesc" rows="3" placeholder="Enter role description">{{ $selectedRole['description'] }}</textarea>
                        </div>

                        <h6 class="mb-3">Permissions</h6>
                        <div class="row">
                            @foreach ($permissions as $permission)
                                <div class="col-md-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="perm_{{ $permission['name'] }}"
                                            @if (in_array($permission['name'], $selectedRole['permissions'])) checked @endif>
                                        <label class="form-check-label" for="perm_{{ $permission['name'] }}">
                                            {{ $permission['label'] }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex gap-2 mt-4">
                            <button class="btn btn-primary">
                                <i class="bx bx-save"></i> Save Changes
                            </button>
                            @if ($selectedRole['id'] > 4)
                                <button class="btn btn-danger">
                                    <i class="bx bx-trash"></i> Delete Role
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="bx bx-info-circle"></i> Select a role from the left panel to manage its permissions.
                        </div>
                    @endif
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

