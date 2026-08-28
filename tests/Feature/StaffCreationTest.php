<?php

use App\Http\Middleware\EnsureCompanyOnboardingComplete;
use App\Jobs\SendStaffCredentialsEmail;
use App\Mail\StaffCredentials;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    $this->withoutMiddleware(EnsureCompanyOnboardingComplete::class);
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

test('company owner can create staff and the credentials email job is queued', function () {
    $company = Company::create([
        'name' => 'Acme',
        'slug' => 'acme',
        'staff_limit' => 5,
    ]);
    $owner = User::factory()->create([
        'company_id' => $company->id,
        'user_type' => 'owner',
    ]);
    Queue::fake();

    $response = $this->actingAs($owner)->post(route('staff.store'), staffPayload());

    $response->assertRedirect(route('staff-manage', absolute: false));
    $staff = User::query()->where('email', 'jane@example.com')->firstOrFail();
    expect($staff->company_id)->toBe($company->id)
        ->and($staff->user_type)->toBe('staff')
        ->and(Hash::needsRehash($staff->password))->toBeFalse();
    expect($staff->password)->not->toBe('');

    Queue::assertPushed(SendStaffCredentialsEmail::class, function (SendStaffCredentialsEmail $job) use ($staff): bool {
        return $job->user->is($staff) && Hash::check($job->temporaryPassword, $staff->password);
    });
});

test('send staff credentials email job sends email to staff user', function () {
    Mail::fake();
    $staff = User::factory()->create([
        'email' => 'staff@example.com',
    ]);
    $temporaryPassword = 'secret-password-123';

    $job = new SendStaffCredentialsEmail($staff, $temporaryPassword);
    $job->handle();

    Mail::assertSent(StaffCredentials::class, function (StaffCredentials $mail) use ($staff, $temporaryPassword): bool {
        return $mail->hasTo($staff->email) && $mail->temporaryPassword === $temporaryPassword;
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

test('company owner can view edit staff page and update staff details', function () {
    $company = Company::create([
        'name' => 'Acme',
        'slug' => 'acme',
        'staff_limit' => 5,
    ]);
    $owner = User::factory()->create([
        'company_id' => $company->id,
        'user_type' => 'owner',
    ]);
    $staff = User::factory()->create([
        'company_id' => $company->id,
        'user_type' => 'staff',
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    $this->actingAs($owner)->get(route('staff.edit', $staff))->assertOk();

    $response = $this->actingAs($owner)->put(route('staff.update', $staff), staffPayload([
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]));

    $response->assertRedirect(route('staff-manage', absolute: false));
    expect($staff->fresh()->name)->toBe('Updated Name')
        ->and($staff->fresh()->email)->toBe('updated@example.com');
});

test('company owner can resend password to staff member', function () {
    $company = Company::create([
        'name' => 'Acme',
        'slug' => 'acme',
        'staff_limit' => 5,
    ]);
    $owner = User::factory()->create([
        'company_id' => $company->id,
        'user_type' => 'owner',
    ]);
    $staff = User::factory()->create([
        'company_id' => $company->id,
        'user_type' => 'staff',
        'email' => 'staff-resend@example.com',
    ]);
    $oldPassword = $staff->password;
    Queue::fake();

    $response = $this->actingAs($owner)->post(route('staff.resend-password', $staff));

    $response->assertRedirect(route('staff-manage', absolute: false));
    expect($staff->fresh()->password)->not->toBe($oldPassword);

    Queue::assertPushed(SendStaffCredentialsEmail::class, function (SendStaffCredentialsEmail $job) use ($staff): bool {
        return $job->user->is($staff);
    });
});
