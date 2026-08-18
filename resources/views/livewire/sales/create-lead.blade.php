<?php

use Livewire\Volt\Component;

new class extends Component {
    public $name = '';
    public $email = '';
    public $phone = '';
    public $company = '';
    public $value = '';
    public $status = 'Prospecting';

    public function saveLead()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'value' => 'nullable|numeric',
            'status' => 'required|string',
        ]);

        // Save the lead (implement your logic here)
        session()->flash('message', 'Lead created successfully!');
        $this->reset();
    }
}; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-3 mb-4">Create New Lead</h4>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form wire:submit="saveLead">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name *</label>
                            <input type="text" class="form-control" id="name" wire:model="name" placeholder="John Doe">
                            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" wire:model="email" placeholder="john@example.com">
                            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" wire:model="phone" placeholder="+1 (555) 000-0000">
                            @error('phone') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="company" class="form-label">Company</label>
                            <input type="text" class="form-control" id="company" wire:model="company" placeholder="Company Name">
                            @error('company') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="value" class="form-label">Deal Value ($)</label>
                            <input type="number" class="form-control" id="value" wire:model="value" placeholder="0.00">
                            @error('value') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" wire:model="status">
                                <option value="Prospecting">Prospecting</option>
                                <option value="Qualified">Qualified</option>
                                <option value="Negotiation">Negotiation</option>
                                <option value="Closed">Closed</option>
                            </select>
                            @error('status') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> Save Lead
                            </button>
                            <a href="{{ route('sales-all-list') }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title mb-3">Lead Information Tips</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="bx bx-check text-success"></i> Make sure to fill all required fields
                        </li>
                        <li class="mb-2">
                            <i class="bx bx-check text-success"></i> Use valid email address for communication
                        </li>
                        <li class="mb-2">
                            <i class="bx bx-check text-success"></i> Provide accurate deal value
                        </li>
                        <li class="mb-2">
                            <i class="bx bx-check text-success"></i> Set proper status for tracking
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

