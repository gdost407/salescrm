<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $printTitle }}</title>
<link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
<style>
body { background: white; color: #222; }
.print-document { max-width: 1100px; margin: 24px auto; }
@page { size: A4; margin: 12mm; }
@media print {
    .d-print-none { display:none !important; }
    .print-document { margin:0; max-width:none; }
    .card { border:0; box-shadow:none; }
    .card-body { padding:0 !important; }
    .table-responsive { overflow:visible; }
    table { width:100%; font-size:11px; }
    thead { display:table-header-group; }
    tr { break-inside:avoid; }
    a { color:inherit; text-decoration:none; }
}
</style></head><body>
<main class="print-document">
<div class="d-print-none mb-3"><button type="button" class="btn btn-primary" onclick="window.print()">Print / Save PDF</button></div>
@yield('print-content')
</main></body></html>
