<form method="POST" action="{{ $record->exists ? route($resource.'.update', $record->id) : route($resource.'.store') }}" enctype="multipart/form-data">
@csrf
@if($record->exists) @method('PUT') @endif
<div class="row">
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'name', 'label' => 'Name', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'type', 'label' => 'Type', 'inputType' => 'select', 'options' => ['service' => 'Service (GST exclusive)', 'inventory' => 'Inventory (GST inclusive)'], 'value' => $record->type ?? 'service'])</div>
<div class="col-12 col-md-6">@include('web.partials.field', ['field' => 'description', 'label' => 'Description', 'inputType' => 'textarea'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'hsn', 'label' => 'HSN / SAC', 'inputType' => 'text', 'value' => $record->hsn_sac])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'sku', 'label' => 'SKU', 'inputType' => 'text'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => true, 'field' => 'rate', 'label' => 'Rate', 'inputType' => 'number'])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'is_active', 'label' => 'Status', 'inputType' => 'select', 'options' => ['0' => 'Inactive', '1' => 'Active'], 'value' => $record->exists ? $record->is_active : 1])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['field' => 'tax_id', 'label' => 'Tax', 'inputType' => 'select', 'options' => ['' => 'Select tax or enter GST rate below'] + $taxes->mapWithKeys(fn ($tax) => [$tax->id => $tax->name.' ('.$tax->rate.'%)'])->all()])</div>
<div class="col-12 col-md-6 col-lg-4">@include('web.partials.field', ['required' => ! old('tax_id', $record->tax_id), 'field' => 'gst_rate', 'label' => 'GST rate (%) — used when no tax is selected', 'inputType' => 'number', 'step' => '0.0001', 'value' => $record->tax?->rate ?? '0'])</div>
<div class="col-12 col-md-6"><div class="mb-6"><label class="form-label" for="image">Image (JPG, PNG or WebP, up to 2 MB)</label><div class="input-group input-group-merge"><span class="input-group-text" aria-hidden="true"><i class="icon-base bx bx-image"></i></span><input class="form-control" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp"></div></div></div>
@if($record->image)<div class="form-check mb-3"><input class="form-check-input" id="remove_image" name="remove_image" type="checkbox" value="1"><label for="remove_image" class="form-check-label">Remove current image</label></div>@endif
</div>
<div class="d-flex flex-wrap gap-2"><button type="submit" class="btn btn-primary"><i class="bx bx-save me-1" aria-hidden="true"></i>Save Item</button>
@if(auth()->user()->hasPermission('view_'.$permission))<a href="{{ route($resource.'.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1" aria-hidden="true"></i>Back to list</a>@endif
</div></form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const tax = document.getElementById('tax_id');
    const rate = document.getElementById('gst_rate');
    const updateRequired = () => {
        rate.required = tax.value === '';
        rate.closest('.input-group').querySelector('.input-group-text').classList.toggle('text-danger', rate.required);
    };
    tax.addEventListener('change', updateRequired);
    updateRequired();
});
</script>
@endpush
