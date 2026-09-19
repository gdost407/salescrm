<?php

namespace App\Actions;

use App\Models\TxnLedger;
use App\Models\TxnPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveFinancialEntry
{
    /** @param array<string, mixed> $data */
    public function handle(User $user, array $data, TxnPayment|TxnLedger $record): TxnPayment|TxnLedger
    {
        return DB::transaction(function () use ($user, $data, $record): TxnPayment|TxnLedger {
            if ($record->exists) {
                $record = $record->newQuery()->where('company_id', $user->company_id)->lockForUpdate()->findOrFail($record->id);
            }
            if (! empty($data['document_type'])) {
                $model = SalesDocumentChoices::MODELS[$data['document_type']];
                $document = $model::forCompany($user->company_id)->lockForUpdate()->find($data['document_id']);
                if (! $document || (! empty($data['client_id']) && (int) $document->client_id !== (int) $data['client_id'])) {
                    throw ValidationException::withMessages(['document_id' => 'This document has changed. Select a document belonging to this client.']);
                }
            }
            unset($data['document']);
            $record->fill($data);
            if (! $record->exists) {
                $record->company_id = $user->company_id;
                $record->{$record instanceof TxnPayment ? 'received_by' : 'created_by'} = $user->id;
            }
            $record->save();

            return $record;
        });
    }
}
