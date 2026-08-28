@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">All Staff List</h5>
      <small class="text-body-secondary d-flex float-end">
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-search"></i></span>
          <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Search" aria-label="Search" aria-describedby="basic-icon-default-fullname2">
        </div> &nbsp;
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd" aria-controls="offcanvasEnd"><i class="icon-base bx bx-filter-alt"></i></button> &nbsp;
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createLeadModal"><i class="icon-base bx bx-save"></i></button> &nbsp;
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createLeadModal"><i class="icon-base bx bx-import"></i></button>
      </small>
    </div>
    <div class="card-body">
      @if (session('message'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          {{ session('message') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <table class="table table-bordered responsive-leads-table">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Full Name</th>
            <th scope="col">Email</th>
            <th scope="col">Mobile Number</th>
            <th scope="col">Department</th>
            <th scope="col">Role</th>
            <th scope="col">Status</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($staffMembers as $index => $staff)
            <tr>
              <th scope="row" data-label="#">{{ $index + 1 }}</th>
              <td data-label="Full Name">{{ $staff->name }}</td>
              <td data-label="Email" class="hide-in-mobile-card">{{ $staff->email }}</td>
              <td data-label="Mobile Number">{{ $staff->mobile ?: '-' }}</td>
              <td data-label="Department">{{ $staff->department ?: '-' }}</td>
              <td data-label="Role">{{ $staff->job_role ?: '-' }}</td>
              <td data-label="Status">
                <span class="badge {{ $staff->is_active ? 'bg-label-success' : 'bg-label-secondary' }}">
                  {{ $staff->is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td data-label="Actions">
                <div class="d-flex align-items-center gap-2">
                  <a href="{{ route('staff.edit', $staff) }}" class="btn btn-sm btn-outline-primary" title="Edit Staff Member">
                    <i class="icon-base bx bx-edit-alt"></i> Edit
                  </a>
                  <form action="{{ route('staff.resend-password', $staff) }}" method="POST" class="d-inline" onsubmit="return confirm('Send new password email to {{ $staff->name }} ({{ $staff->email }})?');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-warning" title="Send Password to Staff User">
                      <i class="icon-base bx bx-key"></i> Send Password
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center">No staff members found.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasEndLabel" class="offcanvas-title">Filter</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close" ></button>
  </div>
  <div class="offcanvas-body mx-0 flex-grow-0">
    <form action="" method="post">
      <div class="mb-6">
        <label class="form-label" for="basic-icon-default-fullname">Date Range</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-user"></i></span>
          <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
            <option value="today" selected>Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="year">This Year</option>
            <option value="custom">Custom</option>
          </select>
        </div>
      </div>
      <div class="mb-6">
        <label class="form-label" for="basic-icon-default-fullname">Lead Stage</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-user"></i></span>
          <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
            @php
            $stage = ['Lead', 'Other'];
            foreach ($stage as $s) {
                $selected = old('stage') == $s ? 'selected' : '';
                echo "<option value=\"$s\" $selected>$s</option>";
            }
            @endphp
          </select>
        </div>
      </div>
      <div class="mb-6">
        <label class="form-label" for="basic-icon-default-fullname">Lead Status</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-user"></i></span>
          <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
            @php
            $status = ['New', 'Open', 'In Progress', 'Archived', 'Rejected', 'Won', 'Lost'];
            foreach ($status as $s) {
                $selected = old('status') == $s ? 'selected' : '';
                echo "<option value=\"$s\" $selected>$s</option>";
            }
            @endphp
          </select>
        </div>
      </div>
      <div class="mb-6">
        <label class="form-label" for="basic-icon-default-fullname">Lead Source</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-user"></i></span>
          <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
            @php
            $source = ['Self', 'Website', 'Referral', 'Google Ads', 'Facebook', 'Instagram', 'Linkedin', 'Twitter', 'Other'];
            foreach ($source as $s) {
                $selected = old('source') == $s ? 'selected' : '';
                echo "<option value=\"$s\" $selected>$s</option>";
            }
            @endphp
          </select>
        </div>
      </div>
      <div class="mb-6">
        <label class="form-label" for="basic-icon-default-fullname">Contact Person</label>
        <div class="input-group input-group-merge">
          <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-user"></i></span>
          <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
            <option value="Self" selected>Self</option>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">Filter</button>
    </form>
  </div>
</div>

@endsection