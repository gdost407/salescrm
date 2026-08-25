<?php

use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.app')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $website = '';
    public string $address = '';
    public string $city = '';
    public string $state = '';
    public string $country = '';
    public string $pincode = '';

    public function mount(): void
    {
        $company = Auth::user()->company;

        abort_unless($company instanceof Company, 403);

        $this->name = $company->name ?? '';
        $this->email = $company->email ?? '';
        $this->phone = $company->phone ?? '';
        $this->website = $company->website ?? '';
        $this->address = $company->address ?? '';
        $this->city = $company->city ?? '';
        $this->state = $company->state ?? '';
        $this->country = $company->country ?? '';
        $this->pincode = $company->pincode ?? '';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:15'],
            'website' => ['nullable', 'url', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'pincode' => ['required', 'string', 'max:8'],
        ]);

        $company = Auth::user()->company;
        abort_unless($company instanceof Company, 403);
        $company->update([
            ...$validated,
            'onboarding_completed_at' => now(),
        ]);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="mx-auto flex w-full max-w-4xl flex-col gap-8">
    <div class="space-y-2">
        <flux:heading size="xl">Set up your company</flux:heading>
        <flux:subheading>Complete your company profile to start using your workspace.</flux:subheading>
    </div>

    <form wire:submit="save" class="space-y-8">
        <div class="grid gap-6 md:grid-cols-2">
            <flux:input wire:model="name" label="Company name" required />
            <flux:input wire:model="email" label="Company email" type="email" />
            <flux:input wire:model="phone" label="Phone number" />
            <flux:input wire:model="website" label="Website" type="url" placeholder="https://example.com" />
        </div>

        <flux:separator />

        <div class="space-y-6">
            <flux:heading size="lg">Company address</flux:heading>
            <flux:textarea wire:model="address" label="Street address" rows="3" required />
            <div class="grid gap-6 md:grid-cols-2">
                <flux:input wire:model="city" label="City" required />
                <flux:input wire:model="state" label="State / Province" required />
                <flux:input wire:model="country" label="Country" required />
                <flux:input wire:model="pincode" label="Postal code" required />
            </div>
        </div>

        <div class="flex justify-end">
            <flux:button type="submit" variant="primary">Save and continue</flux:button>
        </div>
    </form>
</div>
