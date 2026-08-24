@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  @if (session('message'))<div class="alert alert-success">{{ session('message') }}</div>@endif
  <div class="row">
    <div class="col-xl-4 col-lg-5 order-1 order-md-0">
      <div class="card mb-6"><div class="card-body pt-12">
        <div class="user-avatar-section"><div class="d-flex align-items-center flex-column"><img class="img-fluid rounded mb-4" src="../../assets/img/avatars/1.png" height="120" width="120" alt="User avatar"><div class="user-info text-center"><h5>{{ $lead->name }}</h5><span class="badge bg-label-secondary">{{ $lead->status }}</span></div></div></div>
        <div class="d-flex justify-content-around flex-wrap my-6 gap-0 gap-md-3 gap-lg-4"><div class="d-flex align-items-center gap-4"><div class="avatar"><div class="avatar-initial bg-label-primary rounded w-px-40 h-px-40"><i class="icon-base bx bx-check icon-lg"></i></div></div><div><h5 class="mb-0">{{ $activities->count() }}</h5><span>Activities</span></div></div><div class="d-flex align-items-center gap-4"><div class="avatar"><div class="avatar-initial bg-label-primary rounded w-px-40 h-px-40"><i class="icon-base bx bx-customize icon-lg"></i></div></div><div><h5 class="mb-0">{{ $lead->deal_amount ?? '0.00' }}</h5><span>Deal Value</span></div></div></div>
        <h5 class="pb-4 border-bottom mb-4">Details</h5><div class="info-container"><ul class="list-unstyled mb-6"><li class="mb-2"><span class="h6">Job Title:</span> <span>{{ $lead->job_title ?: '-' }}</span></li><li class="mb-2"><span class="h6">Email:</span> <span>{{ $lead->email ?: '-' }}</span></li><li class="mb-2"><span class="h6">Status:</span> <span>{{ $lead->status }}</span></li><li class="mb-2"><span class="h6">Stage:</span> <span>{{ $lead->stage }}</span></li><li class="mb-2"><span class="h6">Source:</span> <span>{{ $lead->source }}</span></li><li class="mb-2"><span class="h6">Contact:</span> <span>{{ $lead->mobile ?: '-' }}</span></li><li class="mb-2"><span class="h6">Assigned To:</span> <span>{{ $lead->assignee?->name ?: 'Unassigned' }}</span></li><li class="mb-2"><span class="h6">Country:</span> <span>{{ $lead->country ?: '-' }}</span></li></ul><div class="d-flex justify-content-center"><a href="{{ route('sales-leads.edit', $lead) }}" class="btn btn-primary me-4">Edit</a><form action="{{ route('sales-leads.destroy', $lead) }}" method="POST" onsubmit="return confirm('Delete this lead?');">@csrf @method('DELETE')<button type="submit" class="btn btn-danger">Delete</button></form></div></div>
      </div></div>
    </div>
    @include('app.sales.lead-tabs')
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
$(document).on('submit', '[data-activity-form], [data-activity-delete], [data-activity-complete]', function (event) {
  event.preventDefault();
  const form = $(this);
  const button = form.find('[type="submit"]');
  button.prop('disabled', true);
  $.ajax({
    url: form.attr('action'),
    method: 'POST',
    data: new FormData(this),
    processData: false,
    contentType: false,
    headers: { 'Accept': 'application/json' },
    success: function (response) {
      const replaceTabs = function () {
        const activeTab = $('.nav-link.active[data-bs-toggle="tab"]').attr('data-bs-target');
        $('#lead-tabs-container').replaceWith($(response.html).filter('#lead-tabs-container'));
        if (activeTab) {
          const activeButton = $('[data-bs-target="' + activeTab + '"]');
          if (activeButton.length) bootstrap.Tab.getOrCreateInstance(activeButton[0]).show();
        }
      };
      const openOffcanvas = document.querySelector('.offcanvas.show');
      if (openOffcanvas) {
        $(openOffcanvas).one('hidden.bs.offcanvas', replaceTabs);
        bootstrap.Offcanvas.getOrCreateInstance(openOffcanvas).hide();
      } else {
        replaceTabs();
      }
    },
    error: function (xhr) {
      const message = xhr.responseJSON?.message || 'Unable to save activity.';
      window.alert(message);
    },
    complete: function () {
      button.prop('disabled', false);
      document.dispatchEvent(new Event('page-loading-finished'));
    }
  });
});

$(document).on('click', '.activity-edit', function () {
  const button = $(this);
  const panel = $(button.data('bs-target'));
  const form = panel.find('[data-activity-form]');
  form.attr('action', button.data('action')).find('[data-method]').val('PUT');
  form.find('[data-summary]').val(button.data('summary') || '');
  const scheduled = button.data('scheduled-at') || '';
  form.find('[name="meeting_scheduled_at"], [name="visit_scheduled_at"]').val(scheduled);
  form.find('[name="followup_date"]').val(scheduled.substring(0, 10));
  form.find('[name="followup_time"]').val(scheduled.substring(11, 16));
});

$(document).on('click', '[data-activity-add]', function () {
  const button = $(this);
  const panel = $(button.data('bs-target'));
  const form = panel.find('[data-activity-form]');
  form.attr('action', form.data('default-action')).find('[data-method]').val('POST');
  form[0].reset();
});
});
</script>
@endsection
