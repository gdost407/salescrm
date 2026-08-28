<?php

namespace App\Actions\Integrations;

use App\Models\Company;
use App\Models\Integration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RotateWebhookApiToken
{
    public function handle(Company $company, User $user): string
    {
        return DB::transaction(function () use ($company, $user): string {
            $lockedCompany = Company::query()
                ->whereKey($company)
                ->lockForUpdate()
                ->firstOrFail();

            Integration::query()
                ->whereBelongsTo($lockedCompany)
                ->where('type', 'webhook')
                ->where('status', true)
                ->update(['status' => false]);

            $token = 'crm_'.Str::random(64);

            Integration::create([
                'company_id' => $lockedCompany->id,
                'name' => 'Webhook API Token',
                'type' => 'webhook',
                'api_key' => hash('sha256', $token),
                'configuration' => [
                    'token_preview' => Str::mask($token, '*', 8, -6),
                    'generated_by' => $user->id,
                ],
                'status' => true,
            ]);

            return $token;
        });
    }
}
