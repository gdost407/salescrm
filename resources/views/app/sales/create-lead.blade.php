@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Lead Creation</h5>
      <small class="text-body-secondary float-end">* Make sure to fill all required fields</small>
    </div>
    <div class="card-body">
      <form action="{{ route('sales-create-lead') }}" method="POST">
        @csrf
        <div class="row">
          <div class="col-sm-4">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Full Name</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-user"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="John Doe" aria-label="John Doe" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Email</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-envelope"></i></span>
                <input type="email" class="form-control" id="basic-icon-default-fullname" placeholder="john.doe@example.com" aria-label="john.doe@example.com" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Mobile Number</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-phone"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="123-456-7890" aria-label="123-456-7890" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-8">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Job Title</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-briefcase"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="John Doe" aria-label="John Doe" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Order Value</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-dollar"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Order value" aria-label="Order value" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Lead Stage</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-star"></i></span>
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
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Lead Status</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-check"></i></span>
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
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Lead Source</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-globe"></i></span>
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
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Contact Person</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-user"></i></span>
                <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
                  <option value="Self" selected>Self</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Address</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-map"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Address" aria-label="Address" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Country</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-globe"></i></span>
                <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
                  <option value="India" selected>India</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">State</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-map"></i></span>
                <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
                  <option value="MH" selected>Maharashtra</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">City</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-map"></i></span>
                <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
                  <option value="Pulgaon" selected>Pulgaon</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Zipcode</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-pin"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="123456" aria-label="123456" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Extra Description</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-note"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Extra details about the lead" aria-label="Extra details about the lead" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-12">
            <div class="mb-6">
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-save"></i> Save Lead
              </button>
              <a href="{{ route('sales-all-list') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back"></i> Cancel
              </a>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection