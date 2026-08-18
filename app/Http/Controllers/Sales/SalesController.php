<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    /**
     * Show the sales kanban board.
     */
    public function kanban()
    {
        $leads = [
            ['id' => 1, 'title' => 'Lead 1', 'status' => 'Prospecting', 'value' => '$5,000'],
            ['id' => 2, 'title' => 'Lead 2', 'status' => 'Qualified', 'value' => '$8,000'],
            ['id' => 3, 'title' => 'Lead 3', 'status' => 'Negotiation', 'value' => '$12,000'],
        ];

        return view('app.sales.kanban', compact('leads'));
    }

    /**
     * Show create lead form.
     */
    public function createLead()
    {
        return view('app.sales.create-lead');
    }

    /**
     * Store a new lead.
     */
    public function storeLead()
    {
        // TODO: Implement lead storage logic
        return redirect()->route('sales-all-list')->with('message', 'Lead created successfully!');
    }

    /**
     * Show all leads list.
     */
    public function allList()
    {
        $leads = [
            ['id' => 1, 'name' => 'John Smith', 'email' => 'john@example.com', 'company' => 'Tech Corp', 'value' => '$5,000', 'status' => 'Prospecting'],
            ['id' => 2, 'name' => 'Jane Doe', 'email' => 'jane@example.com', 'company' => 'Innovation Inc', 'value' => '$8,000', 'status' => 'Qualified'],
            ['id' => 3, 'name' => 'Mike Johnson', 'email' => 'mike@example.com', 'company' => 'Digital Solutions', 'value' => '$12,000', 'status' => 'Negotiation'],
            ['id' => 4, 'name' => 'Sarah Williams', 'email' => 'sarah@example.com', 'company' => 'Future Tech', 'value' => '$15,000', 'status' => 'Closed'],
        ];

        return view('app.sales.all-list', compact('leads'));
    }

    /**
     * Show lead settings.
     */
    public function leadSettings()
    {
        $leadStatuses = [
            ['id' => 1, 'name' => 'Prospecting', 'color' => '#FF9800'],
            ['id' => 2, 'name' => 'Qualified', 'color' => '#2196F3'],
            ['id' => 3, 'name' => 'Negotiation', 'color' => '#1976D2'],
            ['id' => 4, 'name' => 'Closed', 'color' => '#4CAF50'],
        ];

        $leadSources = [
            ['id' => 1, 'name' => 'Website'],
            ['id' => 2, 'name' => 'Email'],
            ['id' => 3, 'name' => 'Phone'],
            ['id' => 4, 'name' => 'Referral'],
        ];

        return view('app.sales.lead-settings', compact('leadStatuses', 'leadSources'));
    }
}
