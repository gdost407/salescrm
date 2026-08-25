<?php

use App\Mail\StaffCredentials;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->withoutMiddleware();
});

function staffPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Jane Staff',
        'email' => 'jane@example.com',
        'mobile' => '1234567890',
        'joining_date' => '2026-08-25',
        'department' => 'Sales',
        'job_role' => 'Employee',
        'address' => '1 Main Street',
        'country' => 'Nigeria',
        'state' => 'Lagos',
        'city' => 'Ikeja',
        'zip_code' => '100001',
        'working_time' => '9:00 AM - 5:00 PM',
        'salary_type' => 'monthly',
        'salary' => '1000',
        'is_active' => '1',
    ], $overrides);
}

test('company owner can create staff and the credentials are queued', function () {
    $company = Company::create([
        'name' => 'Acme',
        'slug' => 'acme',
        'staff_limit' => 5,
    ]);
    $owner = User::factory()->create([
        'company_id' => $company->id,
        'user_type' => 'owner',
    ]);
    Mail::fake();

    $response = $this->actingAs($owner)->post(route('staff.store'), staffPayload());

    $response->assertRedirect(route('staff-manage', absolute: false));
    $staff = User::query()->where('email', 'jane@example.com')->firstOrFail();
    expect($staff->company_id)->toBe($company->id)
        ->and($staff->user_type)->toBe('staff')
        ->and(Hash::needsRehash($staff->password))->toBeFalse();
    expect($staff->password)->not->toBe('');
    Mail::assertQueued(StaffCredentials::class, function (StaffCredentials $mail) use ($staff): bool {
        return $mail->user->is($staff) && Hash::check($mail->temporaryPassword, $staff->password);
    });
});

test('company cannot create staff after reaching its limit', function () {
    $company = Company::create([
        'name' => 'Acme',
        'slug' => 'acme',
        'staff_limit' => 1,
    ]);
    $owner = User::factory()->create([
        'company_id' => $company->id,
        'user_type' => 'owner',
    ]);
    User::factory()->create([
        'company_id' => $company->id,
        'user_type' => 'staff',
        'is_active' => true,
    ]);

    $response = $this->actingAs($owner)->post(route('staff.store'), staffPayload());

    $response->assertUnprocessable();
    expect(User::query()->where('company_id', $company->id)->where('user_type', 'staff')->count())->toBe(1);
});
