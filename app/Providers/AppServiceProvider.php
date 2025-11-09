<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\PackingList;
use App\Observers\PackingListObserver;
use App\Models\Invoice;
use App\Observers\InvoiceObserver;

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
        // Register model observers
        PackingList::observe(PackingListObserver::class);
        Invoice::observe(InvoiceObserver::class);
    }
}
