@if ($details['address'] ?? null)<div class="document-text">{{ $details['address'] }}</div>@endif
<div>{{ implode(', ', array_filter([$details['city'] ?? null, $details['state'] ?? null, $details['country'] ?? null])) }} {{ $details['pincode'] ?? '' }}</div>
@if ($details['email'] ?? null)<div>{{ $details['email'] }}</div>@endif
@if ($details['phone'] ?? null)<div>{{ $details['phone'] }}</div>@endif
