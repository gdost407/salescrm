@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold py-3 mb-0">All Leads</h4>
        </div>
        <div>
            <a href="{{ route('sales-create-lead') }}" class="btn btn-primary">
                <i class="bx bx-plus"></i> Create New Lead
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bx bx-search"></i></span>
                        <input type="text" class="form-control" placeholder="Search leads...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Deal Value</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $lead)
                        <tr>
                            <td>
                                <strong>{{ $lead['name'] }}</strong>
                            </td>
                            <td>{{ $lead['email'] }}</td>
                            <td>{{ $lead['company'] }}</td>
                            <td>
                                <strong>{{ $lead['value'] }}</strong>
                            </td>
                            <td>
                                @switch($lead['status'])
                                    @case('Prospecting')
                                        <span class="badge bg-warning">{{ $lead['status'] }}</span>
                                        @break
                                    @case('Qualified')
                                        <span class="badge bg-info">{{ $lead['status'] }}</span>
                                        @break
                                    @case('Negotiation')
                                        <span class="badge bg-primary">{{ $lead['status'] }}</span>
                                        @break
                                    @case('Closed')
                                        <span class="badge bg-success">{{ $lead['status'] }}</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $lead['status'] }}</span>
                                @endswitch
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-icon btn-text-secondary" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#">
                                            <i class="bx bx-edit-alt"></i> Edit
                                        </a>
                                        <a class="dropdown-item" href="#">
                                            <i class="bx bx-show"></i> View
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item text-danger" href="#">
                                            <i class="bx bx-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                No leads found. <a href="{{ route('sales-create-lead') }}">Create one now</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
