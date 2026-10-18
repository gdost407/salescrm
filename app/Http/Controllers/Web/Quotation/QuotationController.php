<?php

namespace App\Http\Controllers\Web\Quotation;

use App\Http\Controllers\Web\DocumentController;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuotationController extends DocumentController
{
    protected string $type = 'quotation';

    public function searchClients(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = Client::query()
            ->where('company_id', $request->user()->company_id)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if (filled($validated['q'] ?? null)) {
            $search = '%'.$validated['q'].'%';
            $query->where(function ($clientQuery) use ($search): void {
                $clientQuery->where('name', 'like', $search)
                    ->orWhere('mobile', 'like', $search)
                    ->orWhere('email', 'like', $search);
            });
        }

        return response()->json([
            'data' => $query->limit(20)->get([
                'id', 'name', 'company_name', 'email', 'mobile', 'billing_address', 'shipping_address', 'country', 'state', 'city', 'zip_code',
            ]),
        ]);
    }
}
