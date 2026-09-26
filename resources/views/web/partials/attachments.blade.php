@if($editing ?? false)
<div class="mb-6"><label class="form-label" for="attachments">Attachments (PDF or images, up to 5 MB each, 10 per submission)</label>
<div class="input-group input-group-merge"><span class="input-group-text" aria-hidden="true"><i class="icon-base bx bx-paperclip"></i></span><input class="form-control" type="file" id="attachments" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp"></div></div>
@endif
@if($record->exists && $record->attachments->isNotEmpty() && auth()->user()->hasPermission('view_'.$resource))
<div class="mb-3 d-print-none"><h6>Attachments</h6><ul>
@foreach($record->attachments as $attachment)
<li><a href="{{ route($resource.'.attachment', ['document' => $record->id, 'attachment' => $attachment->id]) }}">{{ $attachment->file_name }}</a></li>
@endforeach
</ul></div>
@endif
