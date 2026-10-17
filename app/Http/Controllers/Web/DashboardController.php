<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $canViewLeads = $user->hasPermission('view_own_leads');
        $cards = [];
        $statuses = collect();
        $recentLeads = collect();
        $dashboardFollowups = collect();
        $conversionRate = 0;
        if ($canViewLeads) {
            $leads = Lead::visibleTo($user);
            $counts = (clone $leads)->select('status')->selectRaw('COUNT(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status');
            $total = (int) $counts->sum();
            $new = (int) $counts->filter(fn ($count, $status) => strtolower(trim($status)) === 'new')->sum();
            $converted = (int) $counts->filter(fn ($count, $status) => strtolower(trim($status)) === 'converted')->sum();
            $conversionRate = $total ? round($converted * 100 / $total, 1) : 0;
            $cards = [
                ['label' => 'Total leads', 'count' => $total, 'route' => 'sales-all-list', 'hint' => 'All visible leads'],
                ['label' => 'New leads', 'count' => $new, 'route' => 'sales-all-list', 'hint' => 'Current status: New'],
                ['label' => 'Converted leads', 'count' => $converted, 'route' => 'sales-all-list', 'hint' => 'Current status: Converted'],
            ];
            $statuses = $counts->sortDesc()->map(fn ($count, $status): array => [
                'label' => $status ?: 'Unspecified', 'count' => (int) $count,
                'percentage' => $total ? round($count * 100 / $total, 1) : 0,
            ])->values();
            $recentLeads = (clone $leads)->latest('created_at')->latest('id')->limit(6)->get(['id', 'name', 'company_name', 'status', 'created_at']);

            $followups = LeadActivity::query()
                ->where('company_id', $user->company_id)
                ->where('activity_type', 'followup')
                ->where('status', 'pending')
                ->whereNotNull('scheduled_at')
                ->where(function ($query) {
                    $query->whereNull('metadata->activity_status')
                        ->orWhere('metadata->activity_status', '!=', 'rescheduled');
                })
                ->whereHas('lead', fn ($query) => $query->visibleTo($user))
                ->with(['lead:id,name', 'user:id,name'])
                ->orderBy('scheduled_at');
            $followupCount = (clone $followups)->count();
            $dashboardFollowups = (clone $followups)->limit(8)->get();
            $cards[] = ['label' => 'Pending follow-ups', 'count' => $followupCount, 'route' => 'calendar', 'hint' => 'Scheduled lead follow-ups'];
        }
        if ($user->hasPermission('view_clients')) {
            $cards[] = ['label' => 'Clients', 'count' => Client::where('company_id', $user->company_id)->count(), 'route' => 'clients.index', 'hint' => 'All company clients'];
        }
        if ($user->hasPermission('view_staff')) {
            $cards[] = ['label' => 'Staff', 'count' => User::where('company_id', $user->company_id)->where('user_type', 'staff')->count(), 'route' => 'staff-manage', 'hint' => 'Active and inactive staff'];
        }

        $canPunchAttendance = $user->is_active && $user->company_id && $user->user_type === 'staff';
        $attendance = $canPunchAttendance ? Attendance::dashboardRecord($user) : null;

        return view('dashboard', compact('cards', 'statuses', 'recentLeads', 'dashboardFollowups', 'conversionRate', 'canViewLeads', 'canPunchAttendance', 'attendance'));
    }
}
