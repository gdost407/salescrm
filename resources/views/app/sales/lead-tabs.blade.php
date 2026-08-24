<div class="col-xl-8 col-lg-7 order-0 order-md-1" id="lead-tabs-container">
  <div class="nav-align-top">
    <ul class="nav nav-pills flex-column flex-md-row mb-6 flex-wrap row-gap-2" role="tablist">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#lead-overview" type="button"><i class="icon-base bx bx-user icon-sm me-1"></i>Overview</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#lead-activity" type="button"><i class="icon-base bx bx-list-plus icon-sm me-1"></i>Activity</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#lead-timeline" type="button"><i class="icon-base bx bx-bell icon-sm me-1"></i>Timeline</button></li>
    </ul>
  </div>

  <div class="tab-content p-0">
    <div class="tab-pane fade show active" id="lead-overview" role="tabpanel">
      <div class="card mb-6"><h5 class="card-header">Lead Overview</h5><div class="card-body"><div class="row g-4">
        <div class="col-md-6"><span class="text-body-secondary d-block">Created Date</span><strong>{{ $lead->created_at->format('d M Y, h:i A') }}</strong></div>
        <div class="col-md-6"><span class="text-body-secondary d-block">Last Activity Date</span><strong>{{ $lead->last_activity_at?->format('d M Y, h:i A') ?: 'No activity' }}</strong></div>
        <div class="col-md-6"><span class="text-body-secondary d-block">Last Activity Type</span><strong>{{ $activities->first()?->activity_type ? ucfirst($activities->first()->activity_type) : 'No activity' }}</strong></div>
        <div class="col-md-6"><span class="text-body-secondary d-block">Current Status</span><strong>{{ $lead->status }}</strong></div>
      </div></div></div>
      <div class="card mb-6"><h5 class="card-header">Last 5 Activity Entries</h5><div class="card-body">
        @forelse ($activities->take(5) as $activity)<div class="border-bottom pb-3 mb-3"><strong>{{ $activity->subject }}</strong><small class="text-body-secondary float-end">{{ $activity->created_at->diffForHumans() }}</small><p class="mb-0">{{ $activity->summary ?: '-' }}</p></div>@empty<p class="mb-0 text-body-secondary">No activity recorded for this lead.</p>@endforelse
      </div></div>
    </div>

    <div class="tab-pane fade" id="lead-activity" role="tabpanel">
      <div class="nav-align-top mb-4"><ul class="nav nav-tabs flex-nowrap overflow-auto" role="tablist">
        @foreach (['notes' => 'Note', 'call' => 'Call', 'followup' => 'Followup', 'visit' => 'Visit', 'gmeet' => 'Meet', 'email' => 'Email'] as $type => $label)
          <li class="nav-item"><button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#activity-{{ $type }}" type="button">{{ $label }}</button></li>
        @endforeach
      </ul></div>
      <div class="tab-content p-0">
        <x-sales.activity-panel :lead="$lead" type="notes" label="Add Note" :entries="$activityEntries->get('notes', collect())" />
        <x-sales.activity-panel :lead="$lead" type="call" label="Add Call" :entries="$activityEntries->get('call', collect())" />
        <x-sales.activity-panel :lead="$lead" type="followup" label="Add Followup" :entries="$activityEntries->get('followup', collect())" />
        <x-sales.activity-panel :lead="$lead" type="visit" label="Add Visit" :entries="$activityEntries->get('visit', collect())" />
        <x-sales.activity-panel :lead="$lead" type="gmeet" label="Add Meet" :entries="$activityEntries->get('gmeet', collect())" />
        <x-sales.activity-panel :lead="$lead" type="email" label="Add Email" :entries="$activityEntries->get('email', collect())" />
      </div>
    </div>

    <div class="tab-pane fade" id="lead-timeline" role="tabpanel"><div class="card mb-6"><h5 class="card-header">User Activity Timeline</h5><div class="card-body pt-1"><ul class="timeline mb-0">
      @forelse ($timeline as $activity)
        <li class="timeline-item timeline-item-transparent"><span class="timeline-point timeline-point-primary"></span><div class="timeline-event"><div class="timeline-header mb-3"><h6 class="mb-0">{{ $activity->subject ?: ucfirst($activity->activity_type) }}</h6><small class="text-body-secondary">{{ $activity->created_at->diffForHumans() }}</small></div><p class="mb-2">{{ $activity->summary ?: 'No summary added.' }}</p><small class="text-body-secondary">{{ ucfirst($activity->activity_type) }} by {{ $activity->user?->name ?: 'System' }}</small>@if (($activity->metadata['activity_status'] ?? $activity->status) === 'rescheduled') <span class="badge bg-label-warning ms-2">Rescheduled</span> @elseif ($activity->status === 'completed') <span class="badge bg-label-success ms-2">Completed</span> @endif</div></li>
      @empty
        <li class="timeline-item timeline-item-transparent"><span class="timeline-point timeline-point-primary"></span><div class="timeline-event"><p class="mb-2">No timeline records for this lead.</p></div></li>
      @endforelse
    </ul></div></div></div>
  </div>
</div>
