@props(['lead', 'type', 'label', 'entries'])

@php
  $panelId = 'activity-'.$type;
  $latestNoteOrCall = in_array($type, ['notes', 'call'], true) ? $entries->first()?->id : null;
@endphp
<div class="tab-pane fade {{ $type === 'notes' ? 'show active' : '' }}" id="{{ $panelId }}" role="tabpanel">
  <div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h6 class="mb-0">{{ $label }}</h6>
      <button type="button" class="btn btn-sm btn-primary" data-activity-add data-bs-toggle="offcanvas" data-bs-target="#{{ $panelId }}-form"><i class="bx bx-plus me-1"></i> Add</button>
    </div>
    <div class="card-body">
      @forelse ($entries as $entry)
        <div class="border-bottom pb-3 mb-3">
          <div class="d-flex justify-content-between gap-2">
            <div><strong>{{ $entry->subject ?: $label }}</strong><small class="d-block text-body-secondary">{{ $entry->created_at->diffForHumans() }}</small></div>
            <div class="text-nowrap">
              @php($activityStatus = $entry->metadata['activity_status'] ?? $entry->status)
              @if (($type === 'notes' || $type === 'call') ? $entry->id === $latestNoteOrCall : $activityStatus === 'pending')
                <button type="button" class="btn btn-sm btn-icon btn-outline-primary activity-edit" title="Edit" data-bs-toggle="offcanvas" data-bs-target="#{{ $panelId }}-form" data-action="{{ route('sales-lead-activities.update', [$lead, $entry]) }}" data-summary="{{ $entry->summary }}" data-scheduled-at="{{ $entry->scheduled_at?->format('Y-m-d\\TH:i') }}"><i class="bx bx-edit"></i></button>
              @endif
              @if ($activityStatus === 'pending' && ! in_array($type, ['notes', 'call'], true))
                <button type="button" class="btn btn-sm btn-icon btn-outline-success" title="Complete" data-bs-toggle="offcanvas" data-bs-target="#complete-{{ $entry->id }}"><i class="bx bx-check"></i></button>
                <form action="{{ route('sales-lead-activities.destroy', [$lead, $entry]) }}" method="POST" class="d-inline" data-activity-delete onsubmit="return confirm('Delete this activity?');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete"><i class="bx bx-trash"></i></button>
                </form>
              @endif
            </div>
          </div>
          @if ($activityStatus === 'rescheduled')<span class="badge bg-label-warning">Rescheduled</span>@elseif ($activityStatus === 'completed')<span class="badge bg-label-success">Completed</span>@endif
          @if ($entry->summary)<p class="mb-1 mt-2">{{ $entry->summary }}</p>@endif
          @if ($entry->scheduled_at)<small class="text-body-secondary">Scheduled: {{ $entry->scheduled_at->format('d M Y, h:i A') }}</small>@endif
          @if ($entry->metadata)
            @foreach ($entry->metadata as $key => $value)
              @if ($value !== null && $value !== '')<small class="d-block text-body-secondary">{{ str($key)->replace('_', ' ')->title() }}: {{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</small>@endif
            @endforeach
          @endif
        </div>
      @empty
        <p class="mb-0 text-body-secondary">No {{ strtolower($label) }} entries yet.</p>
      @endforelse
    </div>
  </div>
</div>

@foreach ($entries as $entry)
  @if (($entry->metadata['activity_status'] ?? $entry->status) === 'pending' && ! in_array($type, ['notes', 'call'], true))
    <div class="offcanvas offcanvas-end" tabindex="-1" id="complete-{{ $entry->id }}">
      <div class="offcanvas-header"><h5 class="offcanvas-title">Complete {{ $label }}</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
      <div class="offcanvas-body">
        <form action="{{ route('sales-lead-activities.complete', [$lead, $entry]) }}" method="POST" data-activity-complete>
          @csrf
          <label class="form-label" for="final-note-{{ $entry->id }}">Final Note</label>
          <textarea class="form-control mb-4" id="final-note-{{ $entry->id }}" name="final_note" rows="7" required></textarea>
          <button type="submit" class="btn btn-success">Mark Complete</button>
        </form>
      </div>
    </div>
  @endif
@endforeach

<div class="offcanvas offcanvas-end" tabindex="-1" id="{{ $panelId }}-form">
  <div class="offcanvas-header"><h5 class="offcanvas-title">{{ $label }}</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
  <div class="offcanvas-body">
    <form action="{{ route('sales-lead-activities.store', $lead) }}" method="POST" enctype="multipart/form-data" data-activity-form data-default-action="{{ route('sales-lead-activities.store', $lead) }}">
      @csrf
      <input type="hidden" name="activity_type" value="{{ $type }}">
      <input type="hidden" name="_method" value="POST" data-method>
      @if ($type === 'notes' || $type === 'call')
        <textarea class="form-control mb-4" name="summary" rows="7" placeholder="{{ $type === 'call' ? 'Call note' : 'Note' }}" required data-summary></textarea>
      @elseif ($type === 'followup')
        <textarea class="form-control mb-4" name="summary" rows="5" placeholder="Followup note" required data-summary></textarea>
        <input type="date" class="form-control mb-4" name="followup_date" required><input type="time" class="form-control mb-4" name="followup_time" required>
      @elseif ($type === 'visit')
        <textarea class="form-control mb-3" name="visit_address" placeholder="Address" required></textarea><input class="form-control mb-3" name="visit_country" placeholder="Country" required><input class="form-control mb-3" name="visit_state" placeholder="State" required><input class="form-control mb-3" name="visit_city" placeholder="City" required><input class="form-control mb-3" name="visit_zip" placeholder="Zip code" required><textarea class="form-control mb-3" name="visit_motive" placeholder="Visit motive" required></textarea><div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="mark_as_lead_address" value="1"><label class="form-check-label">Mark as lead address</label></div><input type="datetime-local" class="form-control mb-4" name="visit_scheduled_at" required>
      @elseif ($type === 'gmeet')
        <input type="url" class="form-control mb-3" name="meeting_link" placeholder="Meeting link" required><input type="datetime-local" class="form-control mb-3" name="meeting_scheduled_at" required><textarea class="form-control mb-4" name="meeting_motive" placeholder="Meeting motive" required></textarea>
      @elseif ($type === 'email')
        <input type="text" class="form-control mb-3" name="email_subject" placeholder="Subject" required><textarea class="form-control mb-3" name="email_body" placeholder="Email body" rows="7" required></textarea><input type="file" class="form-control mb-4" name="attachment">
      @endif
      <button type="submit" class="btn btn-primary">Save {{ $label }}</button>
    </form>
  </div>
</div>

