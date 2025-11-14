<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\PackingList;
use App\Observers\PackingListObserver;
use App\Models\Invoice;
use App\Observers\InvoiceObserver;
use App\Models\Indent;
use App\Observers\IndentObserver;
use App\Models\Shipment;
use App\Observers\ShipmentObserver;
use App\Models\BlCorrection;
use App\Observers\BlCorrectionObserver;
use App\Models\Form6Document;
use App\Observers\Form6DocumentObserver;
use App\Models\Form9Document;
use App\Observers\Form9DocumentObserver;

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
        Indent::observe(IndentObserver::class);
        Shipment::observe(ShipmentObserver::class);
        BlCorrection::observe(BlCorrectionObserver::class);
        Form6Document::observe(Form6DocumentObserver::class);
        Form9Document::observe(Form9DocumentObserver::class);
    }
}
