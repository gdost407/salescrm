@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Edit Staff Member</h5>
      <small class="text-body-secondary float-end">* Make sure to fill all required fields</small>
    </div>
    <div class="card-body">
      <form action="{{ route('staff.update', $staff) }}" method="POST">
        @csrf
        @method('PUT')
        @if ($errors->any())
          <div class="alert alert-danger">Please correct the highlighted fields.</div>
        @endif
        <div class="row">
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="name">Full Name</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-user"></i></span>
                <input type="text" name="name" class="form-control" id="name" value="{{ old('name', $staff->name) }}" placeholder="John Doe" required>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="email">Email</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-envelope"></i></span>
                <input type="email" name="email" class="form-control" id="email" value="{{ old('email', $staff->email) }}" placeholder="example@gmail.com" required>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="mobile">Mobile Number</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-phone"></i></span>
                <input type="tel" name="mobile" class="form-control" id="mobile" value="{{ old('mobile', $staff->mobile) }}" placeholder="123-456-7890">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="joining_date">Joining Date</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-calendar"></i></span>
                <input type="date" name="joining_date" class="form-control" id="joining_date" value="{{ old('joining_date', $staff->joining_date?->format('Y-m-d')) }}">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="department">Department</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-pin"></i></span>
                <select name="department" class="form-select" id="department" required>
                  @php
                  $departments = ['Sales', 'Marketing', 'IT', 'HR', 'Finance'];
                  foreach ($departments as $d) {
                    $selected = old('department', $staff->department) == $d ? 'selected' : '';
                    echo "<option value=\"$d\" $selected>$d</option>";
                  }
                  @endphp
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="job_role">Role</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-pin"></i></span>
                <select name="job_role" class="form-select" id="job_role" required>
                  @php
                  $roles = ['Manager', 'Employee', 'Intern'];
                  foreach ($roles as $r) {
                    $selected = old('job_role', $staff->job_role) == $r ? 'selected' : '';
                    echo "<option value=\"$r\" $selected>$r</option>";
                  }
                  @endphp
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="mb-6">
              <label class="form-label" for="address">Address</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-map"></i></span>
                <input type="text" name="address" class="form-control" id="address" value="{{ old('address', $staff->address) }}" placeholder="Please enter address">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="country">Country</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-globe"></i></span>
                <select name="country" class="form-select" id="country">
                  @php
                  $countries = ['Nigeria', 'Ghana', 'Togo', 'Benin'];
                  foreach ($countries as $c) {
                    $selected = old('country', $staff->country) == $c ? 'selected' : '';
                    echo "<option value=\"$c\" $selected>$c</option>";
                  }
                  @endphp
                </select>
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="state">State</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-map"></i></span>
                <input type="text" name="state" class="form-control" id="state" value="{{ old('state', $staff->state) }}" placeholder="State">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="city">City</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-map"></i></span>
                <input type="text" name="city" class="form-control" id="city" value="{{ old('city', $staff->city) }}" placeholder="City">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="zip_code">Zip Code</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-pin"></i></span>
                <input type="text" name="zip_code" class="form-control" id="zip_code" value="{{ old('zip_code', $staff->zip_code) }}" placeholder="Zip code">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="working_time">Working Time</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-time"></i></span>
                <input type="text" name="working_time" class="form-control" id="working_time" value="{{ old('working_time', $staff->working_time) }}" placeholder="9:00 AM - 5:00 PM">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="salary_type">Salary Type</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-dollar"></i></span>
                <select name="salary_type" class="form-select" id="salary_type">
                  @php
                  $salaryTypes = ['Monthly', 'Weekly', 'Daily', 'Hourly'];
                  foreach ($salaryTypes as $s) {
                    $salaryValue = strtolower($s);
                    $selected = old('salary_type', $staff->salary_type) == $salaryValue ? 'selected' : '';
                    echo "<option value=\"$salaryValue\" $selected>$s</option>";
                  }
                  @endphp
                </select>
              </div>
            </div>
          </div>  
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="salary">Salary</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-dollar"></i></span>
                <input type="number" name="salary" class="form-control" id="salary" value="{{ old('salary', $staff->salary) }}" min="0" step="0.01" placeholder="Salary">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="is_active">Is Active</label>
              <div class="input-group input-group-merge">
                <span class="input-group-text text-danger"><i class="icon-base bx bx-check"></i></span>
                <select name="is_active" class="form-select" id="is_active">
                  <option value="1" @selected(old('is_active', $staff->is_active ? '1' : '0') == '1')>Yes</option>
                  <option value="0" @selected(old('is_active', $staff->is_active ? '1' : '0') == '0')>No</option>
                </select>
              </div>
            </div>
          </div>

          <div class="col-sm-12">
            <div class="mb-6">
              <button type="submit" class="btn btn-primary">
                <i class="bx bx-save"></i> Update Staff
              </button>
              <a href="{{ route('staff-manage') }}" class="btn btn-secondary">
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
