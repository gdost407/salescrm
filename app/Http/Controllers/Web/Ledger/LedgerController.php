<?php

namespace App\Http\Controllers\Web\Ledger;

use App\Actions\SalesDocumentChoices;
use App\Actions\SaveFinancialEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaveLedgerRequest;
use App\Models\Client;
use App\Models\TxnLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LedgerController extends Controller
{
    public function index(Request $request): View
    {
        $records = TxnLedger::forCompany($request->user()->company_id)->with('client')->latest('id')->paginate(20);

        return view('web.ledger.index', compact('records'));
    }

    public function create(Request $request, SalesDocumentChoices $choices): View
    {
        return $this->form($request, $choices, new TxnLedger, 'create');
    }

    public function store(SaveLedgerRequest $request, SaveFinancialEntry $save): RedirectResponse
    {
        $record = $save->handle($request->user(), $request->validated(), new TxnLedger);

        return $this->saved($request, $record);
    }

    public function show(Request $request, TxnLedger $ledger): View
    {
        $this->owned($request, $ledger);

        return view('web.ledger.show', ['record' => $ledger->load('client')]);
    }

    public function edit(Request $request, TxnLedger $ledger, SalesDocumentChoices $choices): View
    {
        $this->owned($request, $ledger);

        return $this->form($request, $choices, $ledger, 'edit');
    }

    public function update(SaveLedgerRequest $request, TxnLedger $ledger, SaveFinancialEntry $save): RedirectResponse
    {
        $this->owned($request, $ledger);
        $ledger = $save->handle($request->user(), $request->validated(), $ledger);

        return $this->saved($request, $ledger);
    }

    public function destroy(Request $request, TxnLedger $ledger): RedirectResponse
    {
        $this->owned($request, $ledger);
        $ledger->delete();

        return redirect()->route($request->user()->hasPermission('view_ledger') ? 'ledger.index' : 'dashboard')->with('success', 'Ledger deleted.');
    }

    private function form(Request $request, SalesDocumentChoices $choices, TxnLedger $record, string $page): View
    {
        $clients = Client::where('company_id', $request->user()->company_id)->orderBy('name')->get(['id', 'name']);
        $documents = $choices->handle($request->user()->company_id);

        return view('web.ledger.'.$page, compact('record', 'clients', 'documents'));
    }

    private function owned(Request $request, TxnLedger $record): void
    {
        abort_unless((int) $record->company_id === (int) $request->user()->company_id, 404);
    }

    private function saved(Request $request, TxnLedger $record): RedirectResponse
    {
        $canView = $request->user()->hasPermission('view_ledger');

        return redirect()->route($canView ? 'ledger.show' : 'ledger.create', $canView ? $record : [])->with('success', 'Ledger saved.');
    }
}
