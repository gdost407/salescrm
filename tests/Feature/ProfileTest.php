<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutMiddleware(ValidateCsrfToken::class);
    $this->photo = fn (string $name = 'photo.png', int $kilobytes = 0): UploadedFile => UploadedFile::fake()->createWithContent(
        $name,
        base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII=').str_repeat('0', $kilobytes * 1024),
    );
});

test('profile endpoints require authentication', function () {
    $this->get(route('settings.profile'))->assertRedirect(route('login'));
    $this->put(route('settings.profile.update'), [])->assertRedirect(route('login'));
    $this->put(route('settings.password.update'), [])->assertRedirect(route('login'));
    $this->get(route('settings.profile.photo'))->assertRedirect(route('login'));
});

test('profile and navbar display the signed in identity and readonly contact fields', function () {
    $company = Company::factory()->create(['onboarding_completed_at' => now()]);
    $role = Role::create(['company_id' => $company->id, 'name' => 'Sales Manager', 'slug' => 'sales-manager', 'status' => true]);
    $user = User::factory()->for($company)->create(['name' => 'Priya Sharma', 'role_id' => $role->id, 'user_type' => 'staff', 'mobile' => '9876543210']);
    $response = $this->actingAs($user)->get(route('settings.profile'));
    $response->assertSuccessful()->assertSee('Priya Sharma')->assertSee('Sales Manager')->assertSee('>Priya</span>', false)
        ->assertSee('Change Password')->assertDontSee('John Doe');
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    expect($xpath->query('//*[@id="profile-email" and @readonly and not(@name)]')->length)->toBe(1);
    expect($xpath->query('//*[@id="profile-mobile" and @readonly and not(@name)]')->length)->toBe(1);
    $this->get(route('settings.password'))->assertRedirect(route('settings.profile').'#change-password');
});

test('self profile updates only personal details even with forged protected fields', function () {
    $user = User::factory()->create(['user_type' => 'staff', 'mobile' => '1234567890']);
    $other = User::factory()->create();
    $original = $user->only(['email', 'mobile', 'company_id', 'role_id', 'user_type', 'salary', 'password', 'profile_photo_path']);
    $this->actingAs($user)->put(route('settings.profile.update'), [
        'name' => 'Updated Name', 'address' => 'New address', 'country' => 'India', 'state' => 'Gujarat', 'city' => 'Surat', 'zip_code' => '395001',
        'id' => $other->id, 'email' => 'changed@example.com', 'mobile' => '9999999999', 'company_id' => 999, 'role_id' => 999,
        'user_type' => 'owner', 'salary' => 999999, 'password' => 'forged-password', 'profile_photo_path' => 'another-user.jpg',
    ])->assertSessionHasNoErrors()->assertRedirect(route('settings.profile'));
    expect($user->fresh()->name)->toBe('Updated Name')
        ->and($user->fresh()->city)->toBe('Surat')
        ->and($user->fresh()->only(array_keys($original)))->toBe($original)
        ->and($other->fresh()->name)->toBe($other->name);
});

test('profile rejects invalid names and unsafe or oversized photos', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $this->actingAs($user)->put(route('settings.profile.update'), ['name' => ''])->assertSessionHasErrors('name');
    foreach ([UploadedFile::fake()->create('photo.svg', 1, 'image/svg+xml'), ($this->photo)('large.png', 2049)] as $photo) {
        $this->put(route('settings.profile.update'), ['name' => $user->name, 'photo' => $photo])->assertSessionHasErrors('photo');
    }
    expect($user->fresh()->profile_photo_path)->toBeNull();
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

test('profile photos can be replaced and are only served to their owner', function () {
    Storage::fake('local');
    $user = User::factory()->create();
    $this->actingAs($user)->put(route('settings.profile.update'), ['name' => $user->name, 'photo' => ($this->photo)('first.png')])->assertSessionHasNoErrors();
    $first = $user->fresh()->profile_photo_path;
    Storage::disk('local')->assertExists($first);
    $this->get(route('settings.profile.photo'))->assertSuccessful()->assertHeader('X-Content-Type-Options', 'nosniff');
    $this->get(route('settings.profile'))->assertSee(route('settings.profile.photo'), false);
    $this->put(route('settings.profile.update'), ['name' => $user->name, 'photo' => ($this->photo)('second.png')])->assertSessionHasNoErrors();
    Storage::disk('local')->assertMissing($first);
    Storage::disk('local')->assertExists($user->fresh()->profile_photo_path);
    $other = User::factory()->create();
    $this->actingAs($other)->get(route('settings.profile.photo', ['user_id' => $user->id]))->assertNotFound();
});

test('password change validates current password and confirmation before saving', function () {
    $user = User::factory()->create();
    $token = $user->remember_token;
    $this->actingAs($user);
    $data = ['current_password' => 'wrong-password', 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123'];
    $this->put(route('settings.password.update'), $data)->assertSessionHasErrors('current_password');
    $data['current_password'] = 'password';
    $data['password_confirmation'] = 'mismatch';
    $this->put(route('settings.password.update'), $data)->assertSessionHasErrors('password');
    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
    $data['password_confirmation'] = $data['password'];
    $this->put(route('settings.password.update'), $data)->assertSessionHasNoErrors()->assertRedirect(route('settings.profile').'#change-password');
    expect(Hash::check('new-password-123', $user->fresh()->password))->toBeTrue()
        ->and($user->fresh()->remember_token)->not->toBe($token);
    $this->assertAuthenticatedAs($user);
});
