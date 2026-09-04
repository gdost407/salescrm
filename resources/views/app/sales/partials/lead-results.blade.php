<div class="card-body">
  <div class="table-responsive">
    <table class="table table-sm table-hover table-bordered align-middle mb-2 responsive-leads-table">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">Name</th>
          <th scope="col">Email</th>
          <th scope="col">Mobile</th>
          <th scope="col">Title</th>
          <th scope="col">Value</th>
          <th scope="col">Stage</th>
          <th scope="col">Status</th>
          <th scope="col">Source</th>
          <th scope="col">Assignee</th>
          <th scope="col">Priority</th>
          <th scope="col">Created</th>
          <th scope="col">Activity</th>
          <th scope="col">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($leads as $lead)
          <tr>
            <th nowrap scope="row" data-label="#">{{ $leads->firstItem() + $loop->index }}</th>
            <td nowrap data-label="Full Name"><a href="{{ route('sales-lead-view', $lead) }}" class="text-body">{{ $lead->name }}</a></td>
            <td nowrap data-label="Email" class="hide-in-mobile-card"><a href="{{ route('sales-lead-view', $lead) }}" class="text-body">{{ $lead->email ?: '-' }}</a></td>
            <td nowrap data-label="Mobile Number"><a href="{{ route('sales-lead-view', $lead) }}" class="text-body">{{ $lead->mobile ?: '-' }}</a></td>
            <td nowrap data-label="Job Title">{{ $lead->job_title ?: '-' }}</td>
            <td nowrap data-label="Order Value">{{ $lead->deal_amount ?? '0.00' }}</td>
            <td nowrap data-label="Lead Stage"><span class="badge bg-label-info">{{ $lead->stage ?: '-' }}</span></td>
            <td nowrap data-label="Lead Status"><span class="badge bg-label-primary">{{ $lead->status ?: '-' }}</span></td>
            <td nowrap data-label="Source">{{ $lead->source ?: '-' }}</td>
            <td nowrap data-label="Contact Person">{{ $lead->assignee?->name ?: '-' }}</td>
            <td nowrap data-label="Priority"><span class="badge bg-label-{{ $lead->priority === 'urgent' ? 'danger' : ($lead->priority === 'high' ? 'warning' : 'secondary') }}">{{ ucfirst($lead->priority ?: 'medium') }}</span></td>
            <td nowrap data-label="Created">{{ $lead->created_at?->format('d M Y') ?: '-' }}</td>
            <td nowrap data-label="Last Activity">{{ $lead->last_activity_at?->format('d M Y') ?: '-' }}</td>
            <td nowrap data-label="Actions">
              <a href="{{ route('sales-leads.edit', $lead) }}" class="btn btn-xs btn-primary" title="Edit lead"><i class="bx bx-edit"></i></a>
              <form action="{{ route('sales-leads.destroy', $lead) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this lead?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-xs btn-danger" title="Delete lead"><i class="bx bx-trash"></i></button>
              </form>
            </td>
          </tr>
        @empty
          <tr><td colspan="14" class="text-center">No leads found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2">
    <small class="text-body-secondary">Showing {{ $leads->firstItem() ?? 0 }} to {{ $leads->lastItem() ?? 0 }} of {{ $leads->total() }}</small>
    {{ $leads->onEachSide(1)->links() }}
  </div>
</div>