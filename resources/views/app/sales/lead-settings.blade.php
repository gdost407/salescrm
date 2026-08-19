@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Leads Settings</h5>
      <small class="text-body-secondary float-end"></small>
    </div>
    <div class="card-body">
      <div class="nav-align-left nav-tabs-shadow">
        <ul class="nav nav-tabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-home" aria-controls="navs-left-home" aria-selected="true">
              Stages
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-profile" aria-controls="navs-left-profile" aria-selected="false" tabindex="-1">
              Statuses
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab" data-bs-target="#navs-left-messages" aria-controls="navs-left-messages" aria-selected="false" tabindex="-1">
              Sources
            </button>
          </li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade active show" id="navs-left-home" role="tabpanel">
            <h5 class="card-title mb-0">Lead Stages</h5>
            <div class="mb-3">
                <label for="newStage" class="form-label">Add New Stage</label>
                <div class="input-group">
                    <input type="text" class="form-control" id="newStage" placeholder="Enter stage name">
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
          <div class="tab-pane fade" id="navs-left-profile" role="tabpanel">
            <h5 class="card-title mb-0">Lead Statuses</h5>
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
          <div class="tab-pane fade" id="navs-left-messages" role="tabpanel">
            <h5 class="card-title mb-0">Lead Sources</h5>
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
  </div>
</div>
@endsection
