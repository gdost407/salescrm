<form method="POST" action="{{ $record->exists ? route($resource.'.update', $record->id) : route($resource.'.store') }}">
@csrf
@if($record->exists) @method('PUT') @endif
<div class="row"><div class="col-lg-8">
@include('web.partials.field', ['field' => 'name', 'label' => 'Name', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'type', 'label' => 'Type', 'inputType' => 'select', 'options' => ['business' => 'Business', 'individual' => 'Individual']])
@include('web.partials.field', ['field' => 'client_code', 'label' => 'Client code', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'company_name', 'label' => 'Company name', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'email', 'label' => 'Email', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'mobile', 'label' => 'Mobile', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'gst_no', 'label' => 'GST number', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'pan_no', 'label' => 'PAN number', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'billing_address', 'label' => 'Billing address', 'inputType' => 'textarea'])
@include('web.partials.field', ['field' => 'shipping_address', 'label' => 'Shipping address', 'inputType' => 'textarea'])
@include('web.partials.field', ['field' => 'country', 'label' => 'Country', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'state', 'label' => 'State', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'city', 'label' => 'City', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'zip_code', 'label' => 'Postal code', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'notes', 'label' => 'Notes', 'inputType' => 'textarea'])
@include('web.partials.field', ['field' => 'is_active', 'label' => 'Status', 'inputType' => 'select', 'options' => ['0' => 'Inactive', '1' => 'Active'], 'value' => $record->exists ? $record->is_active : 1])
<button type="submit" class="btn btn-primary">Save Client</button>
@if(auth()->user()->hasPermission('view_'.$permission))<a href="{{ route($resource.'.index') }}" class="btn btn-outline-secondary">Back to list</a>@endif
</div></div></form>
