<form method="POST" action="{{ $record->exists ? route($resource.'.update', $record->id) : route($resource.'.store') }}" enctype="multipart/form-data">
@csrf
@if($record->exists) @method('PUT') @endif
<div class="row"><div class="col-lg-8">
@include('web.partials.field', ['field' => 'name', 'label' => 'Name', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'type', 'label' => 'Type', 'inputType' => 'select', 'options' => ['service' => 'Service (GST exclusive)', 'inventory' => 'Inventory (GST inclusive)'], 'value' => $record->type ?? 'service'])
@include('web.partials.field', ['field' => 'description', 'label' => 'Description', 'inputType' => 'textarea'])
@include('web.partials.field', ['field' => 'hsn', 'label' => 'HSN / SAC', 'inputType' => 'text', 'value' => $record->hsn_sac])
@include('web.partials.field', ['field' => 'sku', 'label' => 'SKU', 'inputType' => 'text'])
@include('web.partials.field', ['field' => 'rate', 'label' => 'Rate', 'inputType' => 'number'])
@include('web.partials.field', ['field' => 'is_active', 'label' => 'Status', 'inputType' => 'select', 'options' => ['0' => 'Inactive', '1' => 'Active'], 'value' => $record->exists ? $record->is_active : 1])
@include('web.partials.field', ['field' => 'tax_id', 'label' => 'Tax', 'inputType' => 'select', 'options' => ['' => 'Select tax or enter GST rate below'] + $taxes->mapWithKeys(fn ($tax) => [$tax->id => $tax->name.' ('.$tax->rate.'%)'])->all()])
@include('web.partials.field', ['field' => 'gst_rate', 'label' => 'GST rate (%) — used when no tax is selected', 'inputType' => 'number', 'step' => '0.0001', 'value' => $record->tax?->rate ?? '0'])
<div class="mb-3"><label class="form-label" for="image">Image (JPG, PNG or WebP, up to 2 MB)</label><input class="form-control" id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp"></div>
@if($record->image)<div class="form-check mb-3"><input class="form-check-input" id="remove_image" name="remove_image" type="checkbox" value="1"><label for="remove_image" class="form-check-label">Remove current image</label></div>@endif
<button type="submit" class="btn btn-primary">Save Item</button>
@if(auth()->user()->hasPermission('view_'.$permission))<a href="{{ route($resource.'.index') }}" class="btn btn-outline-secondary">Back to list</a>@endif
</div></div></form>
