@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Lead Settings</h5>
    </div>
    <div class="card-body">
      @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
      @endif

      <div class="nav-align-left nav-tabs-shadow">
        <ul class="nav nav-tabs" role="tablist">
          @foreach (['stage' => 'Stages', 'status' => 'Statuses', 'source' => 'Sources'] as $type => $label)
            <li class="nav-item" role="presentation">
              <button type="button" class="nav-link {{ $loop->first ? 'active' : '' }}" role="tab" data-bs-toggle="tab" data-bs-target="#lead-settings-{{ $type }}" aria-controls="lead-settings-{{ $type }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                {{ $label }}
              </button>
            </li>
          @endforeach
        </ul>

        <div class="tab-content">
          @foreach (['stage' => 'Lead Stages', 'status' => 'Lead Statuses', 'source' => 'Lead Sources'] as $type => $label)
            <div class="tab-pane fade {{ $loop->first ? 'active show' : '' }}" id="lead-settings-{{ $type }}" role="tabpanel">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="card-title mb-0">{{ $label }}</h5>
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#leadSettingModal" data-setting-type="{{ $type }}">
                  <i class="bx bx-plus me-1"></i>Add
                </button>
              </div>

              <div class="list-group">
                @forelse ($leadSettings->get($type, collect()) as $setting)
                  <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <span>{{ $setting->name }}</span>
                      <span class="badge {{ $setting->type === 'system' ? 'bg-label-secondary' : 'bg-label-primary' }} ms-2">
                        {{ $setting->type === 'system' ? 'System' : 'Custom' }}
                      </span>
                    </div>
                    @if ($setting->type === 'manual')
                      <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-secondary" title="Edit" data-bs-toggle="modal" data-bs-target="#leadSettingModal" data-setting-id="{{ $setting->id }}" data-setting-url="{{ route('sales-lead-settings.update', $setting) }}" data-setting-type="{{ $setting->setting_type }}" data-setting-name="{{ $setting->name }}">
                          <i class="bx bx-edit"></i>
                        </button>
                        <form class="delete-lead-setting" action="{{ route('sales-lead-settings.destroy', $setting) }}" method="POST" onsubmit="return confirm('Delete this lead setting?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Delete">
                            <i class="bx bx-trash"></i>
                          </button>
                        </form>
                      </div>
                    @endif
                  </div>
                @empty
                  <div class="text-body-secondary">No settings found.</div>
                @endforelse
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="leadSettingModal" tabindex="-1" aria-labelledby="leadSettingModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="leadSettingForm" action="{{ route('sales-lead-settings.store') }}" method="POST">
        @csrf
        <input type="hidden" name="_method" id="leadSettingMethod" value="POST">
        <input type="hidden" name="setting_type" id="leadSettingType" value="stage">
        <div class="modal-header">
          <h5 class="modal-title" id="leadSettingModalLabel">Add lead setting</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <label for="leadSettingName" class="form-label">Name</label>
          <input type="text" class="form-control" id="leadSettingName" name="name" maxlength="255" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save setting</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  const leadSettingForm = document.getElementById('leadSettingForm');

  document.getElementById('leadSettingModal').addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const form = document.getElementById('leadSettingForm');
    const isEdit = button.hasAttribute('data-setting-id');
    const type = button.getAttribute('data-setting-type');

    form.action = isEdit ? button.getAttribute('data-setting-url') : "{{ route('sales-lead-settings.store') }}";
    document.getElementById('leadSettingMethod').value = isEdit ? 'PUT' : 'POST';
    document.getElementById('leadSettingType').value = type;
    document.getElementById('leadSettingName').value = isEdit ? button.getAttribute('data-setting-name') : '';
    document.getElementById('leadSettingModalLabel').textContent = isEdit ? 'Edit lead setting' : 'Add lead setting';
  });

  leadSettingForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    const response = await fetch(leadSettingForm.action, {
      method: 'POST',
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: new FormData(leadSettingForm),
    });

    if (response.ok) {
      window.unlockFormSubmit(leadSettingForm);
      window.location.reload();
      return;
    }

    window.unlockFormSubmit(leadSettingForm);
    const data = await response.json().catch(() => ({}));
    alert(Object.values(data.errors ?? {}).flat().join('\n') || 'Unable to save the setting.');
  });

  document.querySelectorAll('.delete-lead-setting').forEach(function (form) {
    form.addEventListener('submit', async function (event) {
      event.preventDefault();
      const response = await fetch(form.action, {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: new FormData(form),
      });

      if (response.ok) {
        window.location.reload();
      } else {
        window.unlockFormSubmit(form);
        alert('Unable to delete the setting.');
      }
    });
  });
</script>
@endsection
