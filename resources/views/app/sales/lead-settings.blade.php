@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-3 mb-4">Lead Settings</h4>
    </div>

    <div class="row">
        <!-- Lead Statuses -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Lead Statuses</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">Add New Status</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="newStatus" placeholder="Enter status name">
                            <button class="btn btn-outline-primary" type="button">
                                <i class="bx bx-plus"></i> Add
                            </button>
                        </div>
                    </div>

                    <div class="list-group">
                        @foreach ($leadStatuses as $status)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <span class="badge me-2" style="width: 20px; height: 20px; border-radius: 3px;"></span>
                                    <span>{{ $status['name'] }}</span>
                                </div>
                                <button class="btn btn-sm btn-danger">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Lead Sources -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Lead Sources</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="newSource" class="form-label">Add New Source</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="newSource" placeholder="Enter source name">
                            <button class="btn btn-outline-primary" type="button">
                                <i class="bx bx-plus"></i> Add
                            </button>
                        </div>
                    </div>

                    <div class="list-group">
                        @foreach ($leadSources as $source)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span>{{ $source['name'] }}</span>
                                <button class="btn btn-sm btn-danger">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Settings -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">General Settings</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="leadExpiration" class="form-label">Lead Expiration Days</label>
                            <input type="number" class="form-control" id="leadExpiration" value="30" placeholder="30">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="defaultSource" class="form-label">Default Lead Source</label>
                            <select class="form-select" id="defaultSource">
                                <option selected>Website</option>
                                <option>Email</option>
                                <option>Phone</option>
                                <option>Referral</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="autoAssign" checked>
                        <label class="form-check-label" for="autoAssign">
                            Auto-assign leads to team members
                        </label>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="sendNotification" checked>
                        <label class="form-check-label" for="sendNotification">
                            Send email notification for new leads
                        </label>
                    </div>

                    <button class="btn btn-primary">
                        <i class="bx bx-save"></i> Save Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
