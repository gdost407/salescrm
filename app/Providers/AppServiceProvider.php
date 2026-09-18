<?php

namespace App\Providers;

use App\Models\TxnInvoice;
use App\Models\TxnJob;
use App\Models\TxnQuotation;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Relation::morphMap([
            'quotation' => TxnQuotation::class,
            'job' => TxnJob::class,
            'invoice' => TxnInvoice::class,
        ]);
    }
}
