<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        /*
        |--------------------------------------------------------------------------
        | Plans
        |--------------------------------------------------------------------------
        */
        $plans = [
            [
                'name' => 'Single User',
                'slug' => 'single-user',
                'description' => 'CRM plan for a single user.',
                'max_users' => 1,
                'monthly_price' => 10.00,
                'yearly_price' => 100.00,
                'currency' => 'USD',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => '5 Users',
                'slug' => '5-users',
                'description' => 'CRM plan for up to 5 users.',
                'max_users' => 5,
                'monthly_price' => 15.00,
                'yearly_price' => 150.00,
                'currency' => 'USD',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => '20 Users',
                'slug' => '20-users',
                'description' => 'CRM plan for up to 20 users.',
                'max_users' => 20,
                'monthly_price' => 20.00,
                'yearly_price' => 200.00,
                'currency' => 'USD',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('subscription_plans')->updateOrInsert(
                [
                    'slug' => $plan['slug'],
                ],
                [
                    'name' => $plan['name'],
                    'description' => $plan['description'],
                    'max_users' => $plan['max_users'],
                    'monthly_price' => $plan['monthly_price'],
                    'yearly_price' => $plan['yearly_price'],
                    'currency' => $plan['currency'],
                    'is_active' => $plan['is_active'],
                    'sort_order' => $plan['sort_order'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Features
        |--------------------------------------------------------------------------
        */
        $features = [
            [
                'name' => 'Lead Management',
                'slug' => 'lead-management',
                'type' => 'boolean',
                'description' => 'Create and manage leads.',
            ],
            [
                'name' => 'Lead Assignment',
                'slug' => 'lead-assignment',
                'type' => 'boolean',
                'description' => 'Assign leads to staff members.',
            ],
            [
                'name' => 'Follow-ups',
                'slug' => 'followups',
                'type' => 'boolean',
                'description' => 'Schedule and manage lead follow-ups.',
            ],
            [
                'name' => 'Lead Activities',
                'slug' => 'lead-activities',
                'type' => 'boolean',
                'description' => 'Track calls, emails, WhatsApp, visits and other activities.',
            ],
            [
                'name' => 'Lead Attachments',
                'slug' => 'lead-attachments',
                'type' => 'boolean',
                'description' => 'Upload and manage lead attachments.',
            ],
            [
                'name' => 'Client Management',
                'slug' => 'client-management',
                'type' => 'boolean',
                'description' => 'Manage converted clients.',
            ],
            [
                'name' => 'Reports',
                'slug' => 'reports',
                'type' => 'boolean',
                'description' => 'Access CRM reports and analytics.',
            ],
            [
                'name' => 'API Access',
                'slug' => 'api-access',
                'type' => 'boolean',
                'description' => 'Access CRM APIs.',
            ],
            [
                'name' => 'Webhook Integration',
                'slug' => 'webhook-integration',
                'type' => 'boolean',
                'description' => 'Receive leads through webhooks.',
            ],
        ];

        foreach ($features as $index => $feature) {
            DB::table('subscription_features')->updateOrInsert(
                [
                    'slug' => $feature['slug'],
                ],
                [
                    'name' => $feature['name'],
                    'type' => $feature['type'],
                    'description' => $feature['description'],
                    'is_active' => true,
                    'sort_order' => $index + 1,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Plan Features
        |--------------------------------------------------------------------------
        |
        | Currently all features are available in all plans.
        | We can change this later.
        |--------------------------------------------------------------------------
        */

        $planIds = DB::table('subscription_plans')
            ->pluck('id', 'slug');

        $featureIds = DB::table('subscription_features')
            ->pluck('id', 'slug');

        $planFeatureMap = [
            'single-user' => [
                'lead-management',
                'lead-assignment',
                'followups',
                'lead-activities',
                'lead-attachments',
                'client-management',
                'reports',
                'api-access',
                'webhook-integration',
            ],

            '5-users' => [
                'lead-management',
                'lead-assignment',
                'followups',
                'lead-activities',
                'lead-attachments',
                'client-management',
                'reports',
                'api-access',
                'webhook-integration',
            ],

            '20-users' => [
                'lead-management',
                'lead-assignment',
                'followups',
                'lead-activities',
                'lead-attachments',
                'client-management',
                'reports',
                'api-access',
                'webhook-integration',
            ],
        ];

        foreach ($planFeatureMap as $planSlug => $features) {

            foreach ($features as $featureSlug) {

                if (!isset($planIds[$planSlug])) {
                    continue;
                }

                if (!isset($featureIds[$featureSlug])) {
                    continue;
                }

                DB::table('plan_features')->updateOrInsert(
                    [
                        'plan_id' => $planIds[$planSlug],
                        'feature_id' => $featureIds[$featureSlug],
                    ],
                    [
                        'value' => '1',
                        'updated_at' => $now,
                        'created_at' => $now,
                    ]
                );
            }
        }
    }
}