<?php

namespace App\Actions;

use App\Models\TxnQuotation;
use App\Models\User;

class SaveQuotation
{
    public function __construct(private SaveSalesDocument $saveDocument) {}

    public function handle(User $user, array $data, ?TxnQuotation $quotation = null): TxnQuotation
    {
        return $this->saveDocument->handle($user, $data, 'quotation', $quotation);
    }
}
