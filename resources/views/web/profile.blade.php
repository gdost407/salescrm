@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @include('web.partials.alerts')
    <div class="card mb-6">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="mb-0">My Profile</h5>
            <small class="text-body-secondary">Red icons indicate required fields.</small>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="d-flex flex-wrap align-items-center gap-4 mb-6">
                    @include('partials.user-avatar', ['avatarSize' => 'avatar-xl'])
                    <div>
                        <h5 class="mb-1">{{ $record->name }}</h5>
                        <span class="text-body-secondary">{{ $record->roleLabel() }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-6">
                            <label class="form-label" for="photo">Change profile photo</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text" aria-hidden="true"><i class="icon-base bx bx-image"></i></span>
                                <input class="form-control @error('photo') is-invalid @enderror" type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp" aria-describedby="photo-help @error('photo') photo-error @enderror">
                            </div>
                            <small id="photo-help" class="text-body-secondary">JPG, PNG or WebP, up to 2 MB. Save your profile to apply the photo.</small>
                            @error('photo')<div class="invalid-feedback d-block" id="photo-error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">@include('web.partials.field', ['field' => 'name', 'label' => 'Full name', 'required' => true])</div>
                    <div class="col-md-6">
                        <div class="mb-6">
                            <label class="form-label" for="profile-email">Email</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text" aria-hidden="true"><i class="icon-base bx bx-envelope"></i></span>
                                <input type="email" class="form-control" id="profile-email" value="{{ $record->email }}" readonly aria-describedby="contact-help">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-6">
                            <label class="form-label" for="profile-mobile">Mobile number</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text" aria-hidden="true"><i class="icon-base bx bx-phone"></i></span>
                                <input type="tel" class="form-control" id="profile-mobile" value="{{ $record->mobile }}" readonly aria-describedby="contact-help">
                            </div>
                        </div>
                    </div>
                    <p id="contact-help" class="text-body-secondary">Email and mobile number cannot be changed here.</p>
                    <div class="col-12">@include('web.partials.field', ['field' => 'address', 'label' => 'Address', 'inputType' => 'textarea', 'icon' => 'bx-map'])</div>
                    @foreach (['country' => 'Country', 'state' => 'State', 'city' => 'City', 'zip_code' => 'Postal code'] as $field => $label)
                        <div class="col-md-6 col-lg-3">@include('web.partials.field', ['field' => $field, 'label' => $label])</div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary"><i class="bx bx-save me-1" aria-hidden="true"></i>Save Profile</button>
            </form>
        </div>
    </div>
    <div class="card" id="change-password">
        <div class="card-header"><h5 class="mb-0">Change Password</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ route('settings.password.update') }}">
                @csrf
                @method('PUT')
                <div class="row">
                    @foreach (['current_password' => 'Current password', 'password' => 'New password', 'password_confirmation' => 'Confirm new password'] as $field => $label)
                        <div class="col-md-4 mb-6">
                            <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                            <div class="input-group input-group-merge">
                                <span class="input-group-text text-danger" aria-hidden="true"><i class="icon-base bx bx-lock-alt"></i></span>
                                <input type="password" name="{{ $field }}" id="{{ $field }}" class="form-control @error($field) is-invalid @enderror" required autocomplete="{{ $field === 'current_password' ? 'current-password' : 'new-password' }}" @error($field) aria-invalid="true" aria-describedby="{{ $field }}-error" @enderror>
                            </div>
                            @error($field)<div class="invalid-feedback d-block" id="{{ $field }}-error">{{ $message }}</div>@enderror
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary"><i class="bx bx-lock-alt me-1" aria-hidden="true"></i>Update Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
