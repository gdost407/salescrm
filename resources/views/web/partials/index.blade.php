<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ $title }}</h4>
    @if(auth()->user()->hasPermission('create_'.$permission))
    <a class="btn btn-primary" href="{{ route($resource.'.create') }}">Create {{ $singular }}</a>
    @endif
</div>
@include('web.partials.alerts')
<div class="card"><div class="table-responsive">
    <table class="table"><thead><tr>@foreach($columns as $label)<th>{{ $label }}</th>@endforeach<th>Actions</th></tr></thead>
    <tbody>@forelse($records as $record)<tr>
        @foreach($columns as $field => $label)
        @php($cell = data_get($record, $field))
        <td>{{ $cell instanceof \DateTimeInterface ? $cell->format('d M Y') : $cell }}</td>
        @endforeach
        <td>@include('web.partials.actions')</td>
    </tr>@empty<tr><td colspan="{{ count($columns) + 1 }}" class="text-center py-4">No records found.</td></tr>@endforelse</tbody>
    </table>
</div><div class="card-body">{{ $records->links('pagination::bootstrap-5') }}</div></div>
