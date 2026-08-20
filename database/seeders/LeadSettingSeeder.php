<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeadSettingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $settings = [

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */
            [
                'setting_type' => 'status',
                'name' => 'New',
                'type' => 'system',
                'sort_order' => 1,
            ],
            [
                'setting_type' => 'status',
                'name' => 'Open',
                'type' => 'system',
                'sort_order' => 2,
            ],
            [
                'setting_type' => 'status',
                'name' => 'In Progress',
                'type' => 'system',
                'sort_order' => 3,
            ],
            [
                'setting_type' => 'status',
                'name' => 'Follow Up',
                'type' => 'system',
                'sort_order' => 4,
            ],
            [
                'setting_type' => 'status',
                'name' => 'Converted',
                'type' => 'system',
                'sort_order' => 5,
            ],
            [
                'setting_type' => 'status',
                'name' => 'Lost',
                'type' => 'system',
                'sort_order' => 6,
            ],
            [
                'setting_type' => 'status',
                'name' => 'Cancelled',
                'type' => 'system',
                'sort_order' => 7,
            ],

            /*
            |--------------------------------------------------------------------------
            | Stage
            |--------------------------------------------------------------------------
            */
            [
                'setting_type' => 'stage',
                'name' => 'New',
                'type' => 'system',
                'sort_order' => 1,
            ],
            [
                'setting_type' => 'stage',
                'name' => 'Qualification',
                'type' => 'system',
                'sort_order' => 2,
            ],
            [
                'setting_type' => 'stage',
                'name' => 'Contacted',
                'type' => 'system',
                'sort_order' => 3,
            ],
            [
                'setting_type' => 'stage',
                'name' => 'Requirement',
                'type' => 'system',
                'sort_order' => 4,
            ],
            [
                'setting_type' => 'stage',
                'name' => 'Proposal',
                'type' => 'system',
                'sort_order' => 5,
            ],
            [
                'setting_type' => 'stage',
                'name' => 'Negotiation',
                'type' => 'system',
                'sort_order' => 6,
            ],
            [
                'setting_type' => 'stage',
                'name' => 'Closed',
                'type' => 'system',
                'sort_order' => 7,
            ],

            /*
            |--------------------------------------------------------------------------
            | Source
            |--------------------------------------------------------------------------
            */
            [
                'setting_type' => 'source',
                'name' => 'Self',
                'type' => 'system',
                'sort_order' => 1,
            ],
            [
                'setting_type' => 'source',
                'name' => 'Website',
                'type' => 'system',
                'sort_order' => 2,
            ],
            [
                'setting_type' => 'source',
                'name' => 'Facebook',
                'type' => 'system',
                'sort_order' => 3,
            ],
            [
                'setting_type' => 'source',
                'name' => 'Instagram',
                'type' => 'system',
                'sort_order' => 4,
            ],
            [
                'setting_type' => 'source',
                'name' => 'Google',
                'type' => 'system',
                'sort_order' => 5,
            ],
            [
                'setting_type' => 'source',
                'name' => 'Referral',
                'type' => 'system',
                'sort_order' => 6,
            ],
            [
                'setting_type' => 'source',
                'name' => 'WhatsApp',
                'type' => 'system',
                'sort_order' => 7,
            ],
            [
                'setting_type' => 'source',
                'name' => 'Other',
                'type' => 'system',
                'sort_order' => 8,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('lead_settings')->updateOrInsert(
                [
                    'company_id' => null,
                    'setting_type' => $setting['setting_type'],
                    'name' => $setting['name'],
                ],
                [
                    'type' => $setting['type'],
                    'is_active' => true,
                    'sort_order' => $setting['sort_order'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}