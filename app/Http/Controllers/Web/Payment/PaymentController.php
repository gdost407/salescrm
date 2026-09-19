<?php

namespace App\Http\Controllers\Web\Payment;

use App\Actions\SalesDocumentChoices;
use App\Actions\SaveFinancialEntry;
use App\Http\Controllers\Controller;
use App\Http\Requests\SavePaymentRequest;
use App\Models\Client;
use App\Models\TxnPayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $records = TxnPayment::forCompany($request->user()->company_id)->with('client')->latest('id')->paginate(20);

        return view('web.payment.index', compact('records'));
    }

    public function create(Request $request, SalesDocumentChoices $choices): View
    {
        return $this->form($request, $choices, new TxnPayment, 'create');
    }

    public function store(SavePaymentRequest $request, SaveFinancialEntry $save): RedirectResponse
    {
        $record = $save->handle($request->user(), $request->validated(), new TxnPayment);

        return $this->saved($request, $record);
    }

    public function show(Request $request, TxnPayment $payment): View
    {
        $this->owned($request, $payment);

        return view('web.payment.show', ['record' => $payment->load('client')]);
    }

    public function print(Request $request, TxnPayment $payment): View
    {
        $this->owned($request, $payment);

        return view('web.payment.print', ['record' => $payment->load('client', 'company')]);
    }

    public function edit(Request $request, TxnPayment $payment, SalesDocumentChoices $choices): View
    {
        $this->owned($request, $payment);

        return $this->form($request, $choices, $payment, 'edit');
    }

    public function update(SavePaymentRequest $request, TxnPayment $payment, SaveFinancialEntry $save): RedirectResponse
    {
        $this->owned($request, $payment);
        $payment = $save->handle($request->user(), $request->validated(), $payment);

        return $this->saved($request, $payment);
    }

    public function destroy(Request $request, TxnPayment $payment): RedirectResponse
    {
        $this->owned($request, $payment);
        $payment->delete();

        return redirect()->route($request->user()->hasPermission('view_payments') ? 'payments.index' : 'dashboard')->with('success', 'Payment deleted.');
    }

    private function form(Request $request, SalesDocumentChoices $choices, TxnPayment $record, string $page): View
    {
        $clients = Client::where('company_id', $request->user()->company_id)->orderBy('name')->get(['id', 'name']);
        $documents = $choices->handle($request->user()->company_id);

        return view('web.payment.'.$page, compact('record', 'clients', 'documents'));
    }

    private function owned(Request $request, TxnPayment $record): void
    {
        abort_unless((int) $record->company_id === (int) $request->user()->company_id, 404);
    }

    private function saved(Request $request, TxnPayment $record): RedirectResponse
    {
        $canView = $request->user()->hasPermission('view_payments');

        return redirect()->route($canView ? 'payments.show' : 'payments.create', $canView ? $record : [])->with('success', 'Payment saved.');
    }
}
