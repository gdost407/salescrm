<div class="d-flex gap-2 flex-wrap">
@if (auth()->user()->hasPermission('view_'.$permission))
<a class="btn btn-sm btn-outline-primary" href="{{ route($resource.'.show', $record->id) }}">View</a>
@endif
@if (auth()->user()->hasPermission('edit_'.$permission))
<a class="btn btn-sm btn-outline-secondary" href="{{ route($resource.'.edit', $record->id) }}">Edit</a>
@endif
@if (auth()->user()->hasPermission('delete_'.$permission))
<form method="POST" action="{{ route($resource.'.destroy', $record->id) }}" onsubmit="return confirm('Delete this record?')">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
</form>
@endif
</div>
