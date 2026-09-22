@include('web.partials.alerts')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $title }}</h4>
    @include('web.partials.actions')
</div>
<div class="card"><div class="card-body"><dl class="row mb-0">
@foreach($columns as $field => $label)
    @php($cell = data_get($record, $field))
    <dt class="col-sm-3">{{ $label }}</dt><dd class="col-sm-9 text-break">{{ $cell instanceof \DateTimeInterface ? $cell->format('d M Y') : ($cell ?? '—') }}</dd>
@endforeach
</dl></div></div>
