<form method="POST" action="{{ $record->exists ? route($resource.'.update', $record->id) : route($resource.'.store') }}">
@csrf
@if($record->exists) @method('PUT') @endif
<div class="row"><div class="col-lg-8">
@include('web.partials.field', ['field' => 'name', 'label' => 'Name', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'rate', 'label' => 'Tax rate (%)', 'inputType' => 'number', 'step' => '0.0001'])
@include('web.partials.field', ['field' => 'is_active', 'label' => 'Status', 'inputType' => 'select', 'options' => ['0' => 'Inactive', '1' => 'Active'], 'value' => $record->exists ? $record->is_active : 1])
<button type="submit" class="btn btn-primary">Save Tax</button>
@if(auth()->user()->hasPermission('view_'.$permission))<a href="{{ route($resource.'.index') }}" class="btn btn-outline-secondary">Back to list</a>@endif
</div></div></form>
