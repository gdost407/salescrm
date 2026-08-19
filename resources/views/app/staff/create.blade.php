@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
  <div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
      <h5 class="mb-0">Staff Creation</h5>
      <small class="text-body-secondary float-end">* Make sure to fill all required fields</small>
    </div>
    <div class="card-body">
      <form action="" method="POST">
        @csrf
        <div class="row">
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Full Name</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-user"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="John Doe">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Email</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-envelope"></i></span>
                <input type="email" class="form-control" id="basic-icon-default-fullname" placeholder="example@gmail.com" aria-label="example@gmail.com" aria-describedby="basic-icon-default-fullname2">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Mobile Number</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-phone"></i></span>
                <input type="tel" class="form-control" id="basic-icon-default-fullname" placeholder="123-456-7890">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Joining Date</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-calendar"></i></span>
                <input type="date" class="form-control" id="basic-icon-default-fullname">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Department</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-pin"></i></span>
                <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
                  @php
                  $department = ['Sales', 'Marketing', 'IT', 'HR', 'Finance'];
                  foreach ($department as $d) {
                  $selected = old('department') == $d ? 'selected' : '';
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
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-pin"></i></span>
                <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
                  @php
                  $role = ['Manager', 'Employee', 'Intern'];
                  foreach ($role as $r) {
                  $selected = old('role') == $r ? 'selected' : '';
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
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-map"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Please enter your address">
              </div>
            </div>
          </div>
          <!-- country -->
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Country</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-globe"></i></span>
                <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
                  @php
                  $country = ['Nigeria', 'Ghana', 'Togo', 'Benin'];
                  foreach ($country as $c) {
                  $selected = old('country') == $c ? 'selected' : '';
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
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-map"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Please enter your state">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">City</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-map"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Please enter your city">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Zip Code</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-pin"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="123456">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Working Time</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-time"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="9:00 AM - 5:00 PM">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Salary Type</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-dollar"></i></span>
                <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
                  @php
                  $salaryType = ['Monthly', 'Weekly', 'Daily', 'Hourly'];
                  foreach ($salaryType as $s) {
                  $selected = old('salaryType') == $s ? 'selected' : '';
                  echo "<option value=\"$s\" $selected>$s</option>";
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
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-dollar"></i></span>
                <input type="text" class="form-control" id="basic-icon-default-fullname" placeholder="Please enter your salary">
              </div>
            </div>
          </div>
          <div class="col-sm-3">
            <div class="mb-6">
              <label class="form-label" for="basic-icon-default-fullname">Is Active</label>
              <div class="input-group input-group-merge">
                <span id="basic-icon-default-fullname2" class="input-group-text"><i class="icon-base bx bx-check"></i></span>
                <select class="form-select" id="exampleFormControlSelect1" aria-label="Default select example">
                  <option value="1">Yes</option>
                  <option value="0">No</option>
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