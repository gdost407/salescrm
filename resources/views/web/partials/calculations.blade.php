@push('scripts')
<script type="module">
import { initQuotationEditor } from '{{ asset('assets/js/quotation-editor.js') }}';
initQuotationEditor({
    catalog: {{ Illuminate\Support\Js::from($catalog) }},
    snapshots: {{ Illuminate\Support\Js::from($snapshots) }},
    rows: {{ Illuminate\Support\Js::from(old('items', $rows)) }}
});
</script>
@endpush
