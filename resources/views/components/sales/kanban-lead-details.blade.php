<div class="row g-3">
    <div class="col-md-6"><small class="text-body-secondary d-block">Email</small><span>{{ $lead->email ?: 'Not provided' }}</span></div>
    <div class="col-md-6"><small class="text-body-secondary d-block">Mobile</small><span>{{ $lead->mobile ?: 'Not provided' }}</span></div>
    <div class="col-md-6"><small class="text-body-secondary d-block">Company</small><span>{{ $lead->company_name ?: 'Not provided' }}</span></div>
    <div class="col-md-6"><small class="text-body-secondary d-block">Owner</small><span>{{ $lead->assignee?->name ?: 'Unassigned' }}</span></div>
    <div class="col-md-6"><small class="text-body-secondary d-block">Stage</small><span>{{ $lead->stage }}</span></div>
    <div class="col-md-6"><small class="text-body-secondary d-block">Status</small><span>{{ $lead->status }}</span></div>
    <div class="col-12"><small class="text-body-secondary d-block">Description</small><span>{{ $lead->description ?: 'No description added.' }}</span></div>
</div>

<div class="d-flex align-items-center justify-content-between border-top mt-4 pt-3">
    <h6 class="mb-0">Recent activity</h6>
    <button type="button" class="btn btn-sm btn-primary" data-lead-activity="{{ $lead->id }}"><i class="bx bx-plus me-1"></i>Add activity</button>
</div>
<div class="mt-3">
    @forelse ($lead->activities->take(5) as $activity)
        <div class="border-bottom pb-3 mb-3">
            <div class="d-flex justify-content-between gap-2"><strong>{{ $activity->subject ?: ucfirst($activity->activity_type) }}</strong><small class="text-body-secondary">{{ $activity->created_at->diffForHumans() }}</small></div>
            @if ($activity->summary)<p class="mb-1 mt-1">{{ $activity->summary }}</p>@endif
            <small class="text-body-secondary">{{ ucfirst($activity->activity_type) }} by {{ $activity->user?->name ?: 'System' }}</small>
        </div>
    @empty
        <p class="text-body-secondary mb-0">No activity recorded for this lead.</p>
    @endforelse
</div>
