<form method="POST" enctype="multipart/form-data" action="{{ $record->exists ? route($resource.'.update', $record->id) : route($resource.'.store') }}">
@csrf @if($record->exists) @method('PUT') @endif
<div class="row">
<div class="col-md-6">@include('web.partials.field', ['required' => true, 'field' => 'client_id', 'label' => 'Client', 'inputType' => 'select', 'options' => ['' => 'Select client'] + $clients->pluck('name', 'id')->all()])</div>
<div class="col-md-3">@include('web.partials.field', ['required' => true, 'field' => $type.'_date', 'label' => ucfirst($type).' date', 'inputType' => 'date', 'value' => $record->{$type.'_date'} ?? now()->format('Y-m-d')])</div>
<div class="col-md-3">@include('web.partials.field', ['field' => 'status', 'label' => 'Status', 'inputType' => 'select', 'options' => array_combine($statuses, array_map(fn ($status) => ucfirst(str_replace('_', ' ', $status)), $statuses))])</div>
@foreach(\App\Actions\SalesDocumentChoices::FIELDS[$type] as $field)
    @if($field === 'terms') @continue @endif
    <div class="col-md-6">
    @if(isset($linkedDocuments[$field]))
    @include('web.partials.field', ['field' => $field, 'label' => ucfirst(str_replace('_id', '', $field)), 'inputType' => 'select', 'options' => ['' => 'None'] + $linkedDocuments[$field]->pluck(str_replace('_id', '_no', $field), 'id')->all()])
    @else
    @include('web.partials.field', ['field' => $field, 'label' => ucfirst(str_replace('_', ' ', $field)), 'inputType' => 'date'])
    @endif
    </div>
@endforeach
</div>
@include('web.partials.item-rows')
@include('web.partials.totals', ['editing' => true])
<div class="row">
<div class="{{ $type === 'job' ? 'col-12' : 'col-md-6' }}">@include('web.partials.field', ['field' => 'notes', 'label' => 'Notes', 'inputType' => 'textarea'])</div>
@if($type !== 'job') <div class="col-md-6">@include('web.partials.field', ['field' => 'terms', 'label' => 'Terms', 'inputType' => 'textarea'])</div> @endif
</div>
@include('web.partials.attachments', ['editing' => true])
<div class="d-flex flex-wrap gap-2">
<button type="submit" class="btn btn-primary"><i class="bx bx-save me-1" aria-hidden="true"></i>Save {{ ucfirst($type) }}</button>
@if(auth()->user()->hasPermission('view_'.$resource))<a class="btn btn-outline-secondary" href="{{ route($resource.'.index') }}"><i class="bx bx-arrow-back me-1" aria-hidden="true"></i>Back to list</a>@endif
</div>
</form>
@include('web.partials.calculations')
