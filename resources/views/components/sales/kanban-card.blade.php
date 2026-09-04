<article class="kanban-lead card mb-3" draggable="true" data-lead-id="{{ $lead->id }}">
    <div class="card-body p-3">
        <button type="button" class="btn btn-link text-body text-start text-decoration-none p-0 w-100" data-lead-view="{{ $lead->id }}">
            <div class="d-flex justify-content-between gap-2"><h6 class="mb-1 text-truncate">{{ $lead->name }}</h6><span class="badge bg-label-secondary text-capitalize">{{ $lead->priority }}</span></div>
            <p class="small text-body-secondary text-truncate mb-2">{{ $lead->job_title ?: 'Lead' }}</p>
            <div class="d-flex justify-content-between align-items-center gap-2"><strong>{{ $lead->deal_amount ? number_format((float) $lead->deal_amount, 2) : 'No value' }}</strong><span class="badge bg-label-info">{{ $lead->stage ?: '-' }}</span></div>
            <div class="small text-body-secondary mt-2">{{ $lead->source ?: '-' }}</div>
            <div class="small text-body-secondary mt-1"><i class="bx bx-time-five me-1"></i>{{ $lead->activities->first()?->activity_type ? ucfirst($lead->activities->first()->activity_type).' · ' : '' }}{{ $lead->last_activity_at?->format('d M Y, h:i A') ?: 'No activity' }}</div>
        </button>
        <div class="float-end mt-3">
            <div class="dropdown">
                <button type="button" class="btn btn-sm btn-light d-flex align-items-center gap-2" data-bs-toggle="dropdown" data-assignee-button="{{ $lead->id }}" title="Change contact person">
                    <span class="avatar avatar-xs"><span class="avatar-initial rounded-circle bg-label-primary">{{ $lead->assignee?->initials() ?: '?' }}</span></span><span class="text-truncate" style="max-width: 8rem;">{{ $lead->assignee?->name ?: 'Unassigned' }}</span>
                </button>
                <ul class="dropdown-menu">
                    <li><button type="button" class="dropdown-item" data-assign-lead="{{ $lead->id }}" data-assigned-to="">Unassigned</button></li>
                    @foreach ($kanbanUsers as $user)
                        <li><button type="button" class="dropdown-item" data-assign-lead="{{ $lead->id }}" data-assigned-to="{{ $user->id }}">{{ $user->name }}</button></li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</article>
