<article class="kanban-lead card mb-3" draggable="true" data-lead-id="{{ $lead->id }}">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between gap-2">
            <h6 class="mb-1">{{ $lead->name }}</h6>
            <span class="badge bg-label-secondary text-capitalize">{{ $lead->priority }}</span>
        </div>
        <p class="small text-body-secondary mb-2">{{ $lead->job_title ?: 'Lead' }}</p>
        <div class="d-flex justify-content-between align-items-center gap-2">
            <strong>{{ $lead->deal_amount ? number_format((float) $lead->deal_amount, 2) : 'No value' }}</strong>
            <small class="text-body-secondary">{{ $lead->created_at->diffForHumans() }}</small>
        </div>
        <div class="d-flex gap-2 mt-3">
            <button type="button" class="btn btn-sm btn-outline-primary" data-lead-view="{{ $lead->id }}">View</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-lead-activity="{{ $lead->id }}">Activity</button>
        </div>
    </div>
</article>
