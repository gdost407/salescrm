<?php

namespace App\Actions;

use App\Models\Client;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveLead
{
    /** @param array<string, mixed> $attributes */
    public function handle(array $attributes, ?Lead $lead = null): Lead
    {
        return DB::transaction(function () use ($attributes, $lead): Lead {
            if ($lead) {
                $lead = Lead::query()->where('company_id', $lead->company_id)->lockForUpdate()->findOrFail($lead->id);
                $lead->fill($attributes);
                $lead->save();
            } else {
                $lead = Lead::create($attributes);
            }

            if ($lead->status !== 'Converted') {
                return $lead;
            }

            $client = $lead->client_id
                ? Client::query()->where('company_id', $lead->company_id)->find($lead->client_id)
                : null;

            if ($lead->client_id && ! $client) {
                throw ValidationException::withMessages(['status' => 'The linked client does not belong to this company.']);
            }

            if (! $client) {
                $client = Client::create([
                    'company_id' => $lead->company_id,
                    'created_by' => $lead->created_by,
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'mobile' => $lead->mobile,
                    'company_name' => $lead->company_name,
                    'billing_address' => $lead->address,
                    'city' => $lead->city,
                    'state' => $lead->state,
                    'country' => $lead->country,
                    'zip_code' => $lead->pincode,
                    'notes' => $lead->notes ?? $lead->description,
                    'is_active' => true,
                ]);
            }

            $lead->update(['client_id' => $client->id, 'converted_at' => $lead->converted_at ?? now()]);

            return $lead;
        });
    }
}
