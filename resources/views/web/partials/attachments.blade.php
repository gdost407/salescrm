@if($editing ?? false)
<div class="mb-6"><label class="form-label" for="attachments">Attachments (PDF or images, up to 5 MB each, 10 per submission)</label>
<div class="input-group input-group-merge"><span class="input-group-text" aria-hidden="true"><i class="icon-base bx bx-paperclip"></i></span><input class="form-control" type="file" id="attachments" name="attachments[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp"></div></div>
@endif
@if($record->exists && $record->attachments->isNotEmpty() && auth()->user()->hasPermission('view_'.$resource))
<style>
    .document-attachment-preview {
        display: block;
        height: 150px;
        overflow: hidden;
        border-radius: .5rem;
        background: #f1f3f5;
    }
    .document-attachment-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .document-attachment-icon {
        display: flex;
        height: 150px;
        align-items: center;
        justify-content: center;
        border-radius: .5rem;
        background: #f1f3f5;
        color: #566176;
        font-size: 3rem;
    }
</style>
<section class="mb-4 d-print-none" aria-labelledby="document-attachments-title">
    <h5 id="document-attachments-title" class="mb-3">Attachments</h5>
    <div class="row g-3">
        @foreach($record->attachments as $attachment)
            @php
                $previewUrl = route($resource.'.attachment', ['document' => $record->id, 'attachment' => $attachment->id, 'preview' => 1]);
                $downloadUrl = route($resource.'.attachment', ['document' => $record->id, 'attachment' => $attachment->id]);
                $isImage = in_array($attachment->mime_type, ['image/jpeg', 'image/png', 'image/webp'], true);
                $fileType = $attachment->mime_type === 'application/pdf' ? 'PDF document' : ($isImage ? 'Image' : 'File');
                $fileSize = $attachment->file_size >= 1048576
                    ? number_format($attachment->file_size / 1048576, 1).' MB'
                    : number_format($attachment->file_size / 1024, 0).' KB';
            @endphp
            <div class="col-12 col-sm-12">
                <article class="card h-100">
                    @if($isImage)
                        <a class="document-attachment-preview" href="{{ $previewUrl }}" target="_blank" rel="noopener" aria-label="Preview {{ $attachment->file_name }}">
                            <img src="{{ $previewUrl }}" alt="Preview of {{ $attachment->file_name }}" loading="lazy">
                        </a>
                    @else
                        <div class="document-attachment-icon" aria-hidden="true">
                            <i class="bx {{ $attachment->mime_type === 'application/pdf' ? 'bxs-file-pdf' : 'bx-file' }}"></i>
                        </div>
                    @endif
                    <div class="card-body p-3">
                        <a class="d-block fw-semibold text-truncate" href="{{ $downloadUrl }}" title="{{ $attachment->file_name }}">{{ $attachment->file_name }}</a>
                        <small class="text-body-secondary">{{ $fileType }} · {{ $fileSize }}</small>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
</section>
@endif
