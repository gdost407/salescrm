@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row g-4">
    <div class="col-12">
      <div class="card">
        <div class="card-header d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
          <div>
            <h5 class="mb-1">API Token</h5>
            <p class="mb-0 text-body-secondary">Use this token to create and authenticate your webhooks.</p>
          </div>
          <form action="{{ route('integration-api-token.store') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">
              <i class="bx {{ $tokens->contains('status', true) ? 'bx-refresh' : 'bx-key' }} me-1"></i>
              {{ $tokens->contains('status', true) ? 'Generate new token' : 'Generate token' }}
            </button>
          </form>
        </div>

        <div class="card-body">
          @if (session('api_token'))
            <div class="alert alert-warning" role="alert">
              <h6 class="alert-heading mb-2">Copy this token now</h6>
              <p class="mb-3">For security, this is the only time the full token will be shown. Generating another token immediately expires this one.</p>
              <div class="input-group">
                <input type="text" class="form-control font-monospace" value="{{ session('api_token') }}" readonly aria-label="New API token">
                <button type="button" class="btn btn-outline-primary" data-copy-api-token="{{ session('api_token') }}">Copy token</button>
              </div>
            </div>
          @endif

          @if ($tokens->isEmpty())
            <div class="text-center py-5">
              <i class="bx bx-key fs-1 text-primary"></i>
              <h6 class="mt-3">No API token yet</h6>
              <p class="mb-0 text-body-secondary">Generate a permanent token when you are ready to connect a webhook.</p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead>
                  <tr>
                    <th>Token</th>
                    <th>Status</th>
                    <th>Generated</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($tokens as $token)
                    <tr>
                      <td class="font-monospace">{{ data_get($token->configuration, 'token_preview', 'Unavailable') }}</td>
                      <td>
                        <span class="badge {{ $token->status ? 'bg-label-success' : 'bg-label-secondary' }}">
                          {{ $token->status ? 'Active' : 'Expired' }}
                        </span>
                      </td>
                      <td>{{ $token->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
