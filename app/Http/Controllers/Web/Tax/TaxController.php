<?php

namespace App\Http\Controllers\Web\Tax;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveTaxRequest;
use App\Models\Tax;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TaxController extends Controller
{
    public function index(Request $request): View
    {
        $records = Tax::forCompany($request->user()->company_id)->orderBy('name')->paginate(20);

        return view('web.tax.index', compact('records'));
    }

    public function create(): View
    {
        $record = new Tax;

        return view('web.tax.create', compact('record'));
    }

    public function store(SaveTaxRequest $request): RedirectResponse
    {
        $record = Tax::create($request->validated() + ['company_id' => $request->user()->company_id]);

        return $this->saved($request, $record);
    }

    public function show(Request $request, Tax $tax): View
    {
        $this->owned($request, $tax);

        return view('web.tax.show', ['record' => $tax]);
    }

    public function edit(Request $request, Tax $tax): View
    {
        $this->owned($request, $tax);

        return view('web.tax.edit', ['record' => $tax]);
    }

    public function update(SaveTaxRequest $request, Tax $tax): RedirectResponse
    {
        $this->owned($request, $tax);
        $tax->update($request->validated());

        return $this->saved($request, $tax);
    }

    public function destroy(Request $request, Tax $tax): RedirectResponse
    {
        $this->owned($request, $tax);
        foreach (['items', 'historyItems'] as $relation) {
            if ($tax->$relation()->exists()) {
                throw ValidationException::withMessages(['record' => 'This tax is in use. Deactivate it instead of deleting it.']);
            }
        }
        $tax->delete();

        return redirect()->route($request->user()->hasPermission('view_taxes') ? 'taxes.index' : 'dashboard')->with('success', 'Tax deleted.');
    }

    private function owned(Request $request, Tax $record): void
    {
        abort_unless((int) $record->company_id === (int) $request->user()->company_id, 404);
    }

    private function saved(Request $request, Tax $record): RedirectResponse
    {
        return redirect()->route($request->user()->hasPermission('view_taxes') ? 'taxes.show' : 'taxes.create', $request->user()->hasPermission('view_taxes') ? $record : [])->with('success', 'Tax saved.');
    }
}
