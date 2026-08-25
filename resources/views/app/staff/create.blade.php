@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Staff Creation</h5>
      <small class="text-body-secondary float-end">* Make sure to fill all required fields</small>
    </div>
    <div class="card-body">
      <form action="{{ route('staff.store') }}" method="POST">
        @csrf
        @if ($errors->any())
          <div class="alert alert-danger">Please correct the highlighted fields.</div>
        @endif
        <div class="row">
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Full Name</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-user"></i></span>
                <input type="text" name="name" class="form-control" id="name" value="{{ old('name') }}" placeholder="John Doe" required>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Email</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-envelope"></i></span>
                <input type="email" name="email" class="form-control" id="email" value="{{ old('email') }}" placeholder="example@gmail.com" aria-label="example@gmail.com" aria-describedby="basic-icon-default-fullname2" required>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Mobile Number</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-phone"></i></span>
                <input type="tel" name="mobile" class="form-control" id="mobile" value="{{ old('mobile') }}" placeholder="123-456-7890">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Joining Date</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-calendar"></i></span>
                <input type="date" name="joining_date" class="form-control" id="joining_date" value="{{ old('joining_date') }}">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Department</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-pin"></i></span>
                <select name="department" class="form-select" id="department" aria-label="Department" required>
                  @php
                  $department = ['Sales', 'Marketing', 'IT', 'HR', 'Finance'];
                  foreach ($department as $d) {
                  $selected = old('department', 'Sales') == $d ? 'selected' : '';
                  echo "<option value=\"$d\" $selected>$d</option>";
                  }
                  @endphp
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Role</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-pin"></i></span>
                <select name="job_role" class="form-select" id="job_role" aria-label="Role" required>
                  @php
                  $role = ['Manager', 'Employee', 'Intern'];
                  foreach ($role as $r) {
                  $selected = old('job_role', 'Employee') == $r ? 'selected' : '';
                  echo "<option value=\"$r\" $selected>$r</option>";
                  }
                  @endphp
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Address</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-map"></i></span>
                <input type="text" name="address" class="form-control" id="address" value="{{ old('address') }}" placeholder="Please enter your address">
              </div>
            </div>
          </div>
          <!-- country -->
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Country</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-globe"></i></span>
                <select name="country" class="form-select" id="country" aria-label="Country">
                  @php
                  $country = ['Nigeria', 'Ghana', 'Togo', 'Benin'];
                  foreach ($country as $c) {
                  $selected = old('country', 'Nigeria') == $c ? 'selected' : '';
                  echo "<option value=\"$c\" $selected>$c</option>";
                  }
                  @endphp
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">State</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-map"></i></span>
                <input type="text" name="state" class="form-control" id="state" value="{{ old('state') }}" placeholder="Please enter your state">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">City</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-map"></i></span>
                <input type="text" name="city" class="form-control" id="city" value="{{ old('city') }}" placeholder="Please enter your city">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Zip Code</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-pin"></i></span>
                <input type="text" name="zip_code" class="form-control" id="zip_code" value="{{ old('zip_code') }}" placeholder="123456">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Working Time</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-time"></i></span>
                <input type="text" name="working_time" class="form-control" id="working_time" value="{{ old('working_time') }}" placeholder="9:00 AM - 5:00 PM">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Salary Type</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-dollar"></i></span>
                <select name="salary_type" class="form-select" id="salary_type" aria-label="Salary type">
                  @php
                  $salaryType = ['Monthly', 'Weekly', 'Daily', 'Hourly'];
                  foreach ($salaryType as $s) {
                  $salaryValue = strtolower($s);
                  $selected = old('salary_type', 'monthly') == $salaryValue ? 'selected' : '';
                  echo "<option value=\"$salaryValue\" $selected>$s</option>";
                  }
                  @endphp
                </select>
              </div>
            </div>
          </div>  
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Salary</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-dollar"></i></span>
                <input type="number" name="salary" class="form-control" id="salary" value="{{ old('salary') }}" min="0" step="0.01" placeholder="Please enter your salary">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Is Active</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text text-danger"><i class="icon-base bx bx-check"></i></span>
                <select name="is_active" class="form-select" id="is_active" aria-label="Active status">
                  <option value="1" @selected(old('is_active', '1') == '1')>Yes</option>
                  <option value="0" @selected(old('is_active') === '0')>No</option>
                </select>
              </div>
            </div>
          </div>

          <div class="col-sm-12">
            <div class="mb-6">
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-save"></i> Save Staff
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