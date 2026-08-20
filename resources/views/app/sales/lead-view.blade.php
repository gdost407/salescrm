@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
          
  <div class="row">
    <div class="col-xl-4 col-lg-5 order-1 order-md-0">
      <div class="card mb-6">
        <div class="card-body pt-12">
          <div class="user-avatar-section">
            <div class=" d-flex align-items-center flex-column">
              <img class="img-fluid rounded mb-4" src="../../assets/img/avatars/1.png" height="120" width="120" alt="User avatar">
              <div class="user-info text-center">
                <h5>Violet Mendoza</h5>
                <span class="badge bg-label-secondary">Author</span>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-around flex-wrap my-6 gap-0 gap-md-3 gap-lg-4">
            <div class="d-flex align-items-center gap-4">
              <div class="avatar">
                <div class="avatar-initial bg-label-primary rounded w-px-40 h-px-40">
                  <i class="icon-base bx bx-check icon-lg"></i>
                </div>
              </div>
              <div>
                <h5 class="mb-0">1.23k</h5>
                <span>Task Done</span>
              </div>
            </div>
            <div class="d-flex align-items-center gap-4">
              <div class="avatar">
                <div class="avatar-initial bg-label-primary rounded w-px-40 h-px-40">
                  <i class="icon-base bx bx-customize icon-lg"></i>
                </div>
              </div>
              <div>
                <h5 class="mb-0">568</h5>
                <span>Project Done</span>
              </div>
            </div>
          </div>
          <h5 class="pb-4 border-bottom mb-4">Details</h5>
          <div class="info-container">
            <ul class="list-unstyled mb-6">
              <li class="mb-2">
                <span class="h6">Username:</span>
                <span>@violet.dev</span>
              </li>
              <li class="mb-2">
                <span class="h6">Email:</span>
                <span>vafgot@vultukir.org</span>
              </li>
              <li class="mb-2">
                <span class="h6">Status:</span>
                <span>Active</span>
              </li>
              <li class="mb-2">
                <span class="h6">Role:</span>
                <span>Author</span>
              </li>
              <li class="mb-2">
                <span class="h6">Tax id:</span>
                <span>Tax-8965</span>
              </li>
              <li class="mb-2">
                <span class="h6">Contact:</span>
                <span>(123) 456-7890</span>
              </li>
              <li class="mb-2">
                <span class="h6">Languages:</span>
                <span>French</span>
              </li>
              <li class="mb-2">
                <span class="h6">Country:</span>
                <span>England</span>
              </li>
            </ul>
            <div class="d-flex justify-content-center">
              <a href="javascript:;" class="btn btn-primary me-4" data-bs-target="#editUser" data-bs-toggle="modal">Edit</a>
              <a href="javascript:;" class="btn btn-danger">Suspend</a>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="col-xl-8 col-lg-7 order-0 order-md-1">
      <div class="nav-align-top">
        <ul class="nav nav-pills flex-column flex-md-row mb-6 flex-wrap row-gap-2">
          <li class="nav-item">
            <a class="nav-link active" href="javascript:void(0);"><i class="icon-base bx bx-user icon-sm me-1"></i> Overview</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="app-user-view-security.html"><i class="icon-base bx bx-lock-alt icon-sm me-1"></i>Activity</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="app-user-view-billing.html"><i class="icon-base bx bx-detail icon-sm me-1"></i>Follow Up</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="app-user-view-notifications.html"><i class="icon-base bx bx-bell icon-sm me-1"></i>Timeline</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="app-user-view-connections.html"><i class="icon-base bx bx-link icon-sm me-1"></i>Attachments</a>
          </li>
        </ul>
      </div>
      
      <div class="card mb-6">
        <h5 class="card-header">User Activity Timeline</h5>
        <div class="card-body pt-1">
          <ul class="timeline mb-0">
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-primary"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-3">
                  <h6 class="mb-0">12 Invoices have been paid</h6>
                  <small class="text-body-secondary">12 min ago</small>
                </div>
                <p class="mb-2">Invoices have been paid to the company</p>
                <div class="d-flex align-items-center mb-2">
                  <div class="badge bg-lighter rounded d-flex align-items-center">
                    <img src="../../assets//img/icons/misc/pdf.png" alt="img" width="15" class="me-2">
                    <span class="h6 mb-0 text-body">invoices.pdf</span>
                  </div>
                </div>
              </div>
            </li>
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-success"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-3">
                  <h6 class="mb-0">Client Meeting</h6>
                  <small class="text-body-secondary">45 min ago</small>
                </div>
                <p class="mb-2">Project meeting with john @10:15am</p>
                <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                  <div class="d-flex flex-wrap align-items-center mb-50">
                    <div class="avatar avatar-sm me-2">
                      <img src="../../assets/img/avatars/1.png" alt="Avatar" class="rounded-circle">
                    </div>
                    <div>
                      <p class="mb-0 small fw-medium">Lester McCarthy (Client)</p>
                      <small>CEO of ThemeSelection</small>
                    </div>
                  </div>
                </div>
              </div>
            </li>
            <li class="timeline-item timeline-item-transparent">
              <span class="timeline-point timeline-point-info"></span>
              <div class="timeline-event">
                <div class="timeline-header mb-3">
                  <h6 class="mb-0">Create a new project for client</h6>
                  <small class="text-body-secondary">2 Day Ago</small>
                </div>
                <p class="mb-2">6 team members in a project</p>
                <ul class="list-group list-group-flush">
                  <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap border-top-0 p-0">
                    <div class="d-flex flex-wrap align-items-center">
                      <ul class="list-unstyled users-list d-flex align-items-center avatar-group m-0 me-2">
                        <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar pull-up" aria-label="Vinnie Mostowy" data-bs-original-title="Vinnie Mostowy">
                          <img class="rounded-circle" src="../../assets/img/avatars/5.png" alt="Avatar">
                        </li>
                        <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar pull-up" aria-label="Allen Rieske" data-bs-original-title="Allen Rieske">
                          <img class="rounded-circle" src="../../assets/img/avatars/12.png" alt="Avatar">
                        </li>
                        <li data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top" class="avatar pull-up" aria-label="Julee Rossignol" data-bs-original-title="Julee Rossignol">
                          <img class="rounded-circle" src="../../assets/img/avatars/6.png" alt="Avatar">
                        </li>
                        <li class="avatar">
                          <span class="avatar-initial rounded-circle pull-up text-heading" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="3 more">+3</span>
                        </li>
                      </ul>
                    </div>
                  </li>
                </ul>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection