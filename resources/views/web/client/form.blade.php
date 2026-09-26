<form method="POST" action="{{ $record->exists ? route($resource.'.update', $record->id) : route($resource.'.store') }}">
@csrf
@if($record->exists) @method('PUT') @endif
<div class="row">
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'name', 'label' => 'Name', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'type', 'label' => 'Type', 'inputType' => 'select', 'options' => ['business' => 'Business', 'individual' => 'Individual']])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'company_name', 'label' => 'Company name', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'email', 'label' => 'Email', 'inputType' => 'email'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'mobile', 'label' => 'Mobile', 'inputType' => 'tel'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'client_code', 'label' => 'Client code', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'gst_no', 'label' => 'GST number', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'pan_no', 'label' => 'PAN number', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'is_active', 'label' => 'Status', 'inputType' => 'select', 'options' => ['0' => 'Inactive', '1' => 'Active'], 'value' => $record->exists ? $record->is_active : 1])</div>
<div class="col-12 col-md-6">@include('web.partials.field', ['field' => 'billing_address', 'label' => 'Billing address', 'inputType' => 'textarea'])</div>
<div class="col-12 col-md-6">@include('web.partials.field', ['field' => 'shipping_address', 'label' => 'Shipping address', 'inputType' => 'textarea'])</div>
<div class="col-12 col-md-6 col-lg-3">@include('web.partials.field', ['field' => 'country', 'label' => 'Country', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-3">@include('web.partials.field', ['field' => 'state', 'label' => 'State', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-3">@include('web.partials.field', ['field' => 'city', 'label' => 'City', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-3">@include('web.partials.field', ['field' => 'zip_code', 'label' => 'Postal code', 'inputType' => 'text'])</div>
<div class="col-12">@include('web.partials.field', ['field' => 'notes', 'label' => 'Notes', 'inputType' => 'textarea'])</div>
</div>
<div class="d-flex flex-wrap gap-2"><button type="submit" class="btn btn-primary"><i class="bx bx-save me-1" aria-hidden="true"></i>Save Client</button>
@if(auth()->user()->hasPermission('view_'.$permission))<a href="{{ route($resource.'.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1" aria-hidden="true"></i>Back to list</a>@endif
</div></form>
