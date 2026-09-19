<div class="card"><div class="card-body p-4">
<div class="row border-bottom pb-3 mb-4">
<div class="col-7"><h3>{{ $record->company->name }}</h3>
<div>{{ $record->company->address }}</div>
<div>{{ $record->company->city }} {{ $record->company->state }} {{ $record->company->pincode }}</div>
<div>{{ $record->company->email }} {{ $record->company->phone }}</div></div>
<div class="col-5 text-end"><h3 class="text-uppercase">{{ $type }}</h3>
<strong>{{ $record->{$type.'_no'} }}</strong><div>Date: {{ $record->{$type.'_date'}->format('d M Y') }}</div>
<div>{{ ucfirst(str_replace('_', ' ', $record->status)) }}</div>
@foreach(['valid_until' => 'Valid until', 'due_date' => 'Due date', 'start_date' => 'Start date', 'completion_date' => 'Completion date'] as $field => $label)
@if($record->$field)<div>{{ $label }}: {{ $record->$field->format('d M Y') }}</div>@endif
@endforeach
</div></div>
<div class="mb-4"><h6>Bill to</h6><strong>{{ $record->client->name }}</strong>
<div>{{ $record->client->company_name }}</div>
<div style="white-space:pre-line">{{ $record->client->billing_address }}</div>
<div>{{ $record->client->city }} {{ $record->client->state }} {{ $record->client->zip_code }}</div>
<div>{{ $record->client->email }} {{ $record->client->mobile }}</div>
@if($record->client->gst_no)<div>GSTIN: {{ $record->client->gst_no }}</div>@endif
</div>
<div class="table-responsive">
<table class="table table-bordered"><thead><tr><th>#</th><th>Description / HSN</th><th>Qty</th><th>Rate (INR)</th><th>GST %</th><th>Taxable</th><th>GST</th><th>Total</th></tr></thead>
<tbody>@foreach($record->items as $item)<tr>
<td>{{ $loop->iteration }}</td><td><strong>{{ $item->item_name }}</strong><div>{{ $item->description }}</div><small>HSN / SAC: {{ $item->hsn_sac }}</small></td>
<td>{{ $item->qty }}</td><td>{{ $item->rate }}<small class="d-block">GST {{ $item->tax_type }}</small></td><td>{{ $item->tax_rate }}</td><td>{{ $item->taxable_amount }}</td><td>{{ $item->tax_amount }}</td><td>{{ $item->total_amount }}</td>
</tr>@endforeach</tbody></table>
</div>
@include('web.partials.totals')
@if($record->notes)<h6>Notes</h6><p style="white-space:pre-line">{{ $record->notes }}</p>@endif
@if($record->terms)<h6>Terms and conditions</h6><p style="white-space:pre-line">{{ $record->terms }}</p>@endif
<div class="text-end mt-5 pt-4"><strong>For {{ $record->company->name }}</strong><p class="mt-5 mb-0">Authorized signatory</p></div>
</div></div>
