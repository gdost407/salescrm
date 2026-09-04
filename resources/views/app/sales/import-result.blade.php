@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header">
      <h5 class="mb-0">Lead Import Results</h5>
      <p class="mb-0 text-body-secondary">The sheet finished processing. Review the rows that were not inserted below.</p>
    </div>
    <div class="card-body">
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="border rounded p-3 h-100">
            <div class="text-body-secondary">Inserted</div>
            <strong class="fs-4 text-success">{{ $importedCount }}</strong>
          </div>
        </div>
        <div class="col-md-4">
          <div class="border rounded p-3 h-100">
            <div class="text-body-secondary">Duplicates skipped</div>
            <strong class="fs-4 text-warning">{{ $skippedDuplicates }}</strong>
          </div>
        </div>
        <div class="col-md-4">
          <div class="border rounded p-3 h-100">
            <div class="text-body-secondary">Failed validation</div>
            <strong class="fs-4 text-danger">{{ $skippedInvalid }}</strong>
          </div>
        </div>
      </div>

      @if ($failedRows || $duplicateRows)
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle">
            <thead>
              <tr>
                <th>Row</th>
                <th>Status</th>
                <th>Reason</th>
                @foreach ($resultColumns as $label)
                  <th nowrap>{{ $label }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
              @foreach ($failedRows as $failedRow)
                <tr>
                  <td>{{ $failedRow['row'] }}</td>
                  <td><span class="badge bg-label-danger">Failed</span></td>
                  <td nowrap>{{ $failedRow['reason'] }}</td>
                  @foreach ($resultColumns as $key => $label)
                    <td nowrap>{{ filled($failedRow['data'][$key] ?? null) ? $failedRow['data'][$key] : '-' }}</td>
                  @endforeach
                </tr>
              @endforeach
              @foreach ($duplicateRows as $duplicateRow)
                <tr>
                  <td>{{ $duplicateRow['row'] }}</td>
                  <td><span class="badge bg-label-warning">Skipped</span></td>
                  <td nowrap>{{ $duplicateRow['reason'] }}</td>
                  @foreach ($resultColumns as $key => $label)
                    <td nowrap>{{ filled($duplicateRow['data'][$key] ?? null) ? $duplicateRow['data'][$key] : '-' }}</td>
                  @endforeach
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="alert alert-success mb-0">All rows were imported successfully.</div>
      @endif
    </div>
  </div>
</div>
@endsection
