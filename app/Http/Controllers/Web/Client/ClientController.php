<?php

namespace App\Http\Controllers\Web\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $records = Client::where('company_id', $request->user()->company_id)->orderBy('name')->paginate(20);

        return view('web.client.index', compact('records'));
    }

    public function create(): View
    {
        $record = new Client;

        return view('web.client.create', compact('record'));
    }

    public function store(SaveClientRequest $request): RedirectResponse
    {
        $record = Client::create($request->validated() + ['company_id' => $request->user()->company_id, 'created_by' => $request->user()->id]);

        return $this->saved($request, $record);
    }

    public function show(Request $request, Client $client): View
    {
        $this->owned($request, $client);

        return view('web.client.show', ['record' => $client]);
    }

    public function edit(Request $request, Client $client): View
    {
        $this->owned($request, $client);

        return view('web.client.edit', ['record' => $client]);
    }

    public function update(SaveClientRequest $request, Client $client): RedirectResponse
    {
        $this->owned($request, $client);
        $client->update($request->validated());

        return $this->saved($request, $client);
    }

    public function destroy(Request $request, Client $client): RedirectResponse
    {
        $this->owned($request, $client);
        foreach (['leads', 'quotations', 'jobs', 'invoices', 'payments', 'ledgerEntries'] as $relation) {
            if ($client->$relation()->exists()) {
                throw ValidationException::withMessages(['record' => 'This client is in use. Deactivate it instead of deleting it.']);
            }
        }
        $client->delete();

        return redirect()->route($request->user()->hasPermission('view_clients') ? 'clients.index' : 'dashboard')->with('success', 'Client deleted.');
    }

    private function owned(Request $request, Client $record): void
    {
        abort_unless((int) $record->company_id === (int) $request->user()->company_id, 404);
    }

    private function saved(Request $request, Client $record): RedirectResponse
    {
        return redirect()->route($request->user()->hasPermission('view_clients') ? 'clients.show' : 'clients.create', $request->user()->hasPermission('view_clients') ? $record : [])->with('success', 'Client saved.');
    }
}
