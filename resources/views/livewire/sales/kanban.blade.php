<?php

use Livewire\Volt\Component;

new class extends Component {
    public $leads = [];

    public function mount()
    {
        // Initialize with sample data
        $this->leads = [
            ['id' => 1, 'title' => 'Lead 1', 'status' => 'Prospecting', 'value' => '$5,000'],
            ['id' => 2, 'title' => 'Lead 2', 'status' => 'Qualified', 'value' => '$8,000'],
            ['id' => 3, 'title' => 'Lead 3', 'status' => 'Negotiation', 'value' => '$12,000'],
        ];
    }
}; ?>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold py-3 mb-4">Sales Kanban Board</h4>
    </div>

    <div class="row">
        <!-- Prospecting -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <span class="badge bg-warning">Prospecting</span>
                    </h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <div class="card mb-2 cursor-move" style="border-left: 4px solid #ff9800;">
                        <div class="card-body p-2">
                            <h6 class="card-title small mb-1">Lead #1</h6>
                            <p class="text-muted small mb-2">John Smith</p>
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-light text-dark small">$5,000</span>
                                <small class="text-muted">2 days ago</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Qualified -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <span class="badge bg-info">Qualified</span>
                    </h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <div class="card mb-2 cursor-move" style="border-left: 4px solid #2196f3;">
                        <div class="card-body p-2">
                            <h6 class="card-title small mb-1">Lead #2</h6>
                            <p class="text-muted small mb-2">Jane Doe</p>
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-light text-dark small">$8,000</span>
                                <small class="text-muted">5 days ago</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Negotiation -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <span class="badge bg-success">Negotiation</span>
                    </h5>
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <div class="card mb-2 cursor-move" style="border-left: 4px solid #4caf50;">
                        <div class="card-body p-2">
                            <h6 class="card-title small mb-1">Lead #3</h6>
                            <p class="text-muted small mb-2">Mike Johnson</p>
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-light text-dark small">$12,000</span>
                                <small class="text-muted">1 week ago</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

