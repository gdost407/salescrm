<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    /**
     * Show create staff form.
     */
    public function create()
    {
        return view('app.staff.create');
    }

    /**
     * Store a new staff member.
     */
    public function store(Request $request)
    {
        // TODO: Implement staff storage logic
        return redirect()->route('staff-manage')->with('message', 'Staff member created successfully!');
    }

    /**
     * Show manage staff list.
     */
    public function manage()
    {
        $staffMembers = [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'department' => 'Sales', 'role' => 'Sales Executive', 'status' => 'Active'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'department' => 'Sales', 'role' => 'Sales Manager', 'status' => 'Active'],
            ['id' => 3, 'name' => 'Mike Johnson', 'email' => 'mike@example.com', 'department' => 'Marketing', 'role' => 'Team Lead', 'status' => 'Active'],
            ['id' => 4, 'name' => 'Sarah Williams', 'email' => 'sarah@example.com', 'department' => 'Operations', 'role' => 'Administrator', 'status' => 'Inactive'],
        ];

        return view('app.staff.manage', compact('staffMembers'));
    }

    /**
     * Show roles and permissions management.
     */
    public function roles()
    {
        $roles = [
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

        $permissions = [
            ['name' => 'view_leads', 'label' => 'View Leads'],
            ['name' => 'create_leads', 'label' => 'Create Leads'],
            ['name' => 'edit_own_leads', 'label' => 'Edit Own Leads'],
            ['name' => 'edit_team_leads', 'label' => 'Edit Team Leads'],
            ['name' => 'edit_all_leads', 'label' => 'Edit All Leads'],
            ['name' => 'delete_leads', 'label' => 'Delete Leads'],
            ['name' => 'view_reports', 'label' => 'View Reports'],
            ['name' => 'manage_team', 'label' => 'Manage Team'],
        ];

        return view('app.staff.roles', compact('roles', 'permissions'));
    }
}

