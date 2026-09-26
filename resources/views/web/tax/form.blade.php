<form method="POST" action="{{ $record->exists ? route($resource.'.update', $record->id) : route($resource.'.store') }}">
@csrf
@if($record->exists) @method('PUT') @endif
<div class="row">
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'name', 'label' => 'Name', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'icon' => 'bx-percentage', 'field' => 'rate', 'label' => 'Tax rate (%)', 'inputType' => 'number', 'step' => '0.0001'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'is_active', 'label' => 'Status', 'inputType' => 'select', 'options' => ['0' => 'Inactive', '1' => 'Active'], 'value' => $record->exists ? $record->is_active : 1])</div>
</div>
<div class="d-flex flex-wrap gap-2"><button type="submit" class="btn btn-primary"><i class="bx bx-save me-1" aria-hidden="true"></i>Save Tax</button>
@if(auth()->user()->hasPermission('view_'.$permission))<a href="{{ route($resource.'.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1" aria-hidden="true"></i>Back to list</a>@endif
</div></form>
