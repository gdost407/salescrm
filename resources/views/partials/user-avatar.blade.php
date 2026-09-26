<div class="avatar {{ $avatarSize ?? '' }} flex-shrink-0">
    @if (auth()->user()->profile_photo_path)
        <img src="{{ route('settings.profile.photo') }}" alt="Your profile photo" class="rounded-circle w-100 h-100" style="object-fit: cover;">
    @else
        <span class="avatar-initial rounded-circle bg-label-primary" aria-label="Your profile initials">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->initials(), 0, 2)) }}</span>
    @endif
</div>
