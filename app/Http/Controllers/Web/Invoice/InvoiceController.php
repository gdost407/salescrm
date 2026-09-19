<?php

namespace App\Http\Controllers\Web\Invoice;

use App\Http\Controllers\Web\DocumentController;

class InvoiceController extends DocumentController
{
    protected string $type = 'invoice';
}
