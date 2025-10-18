<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ServiceRequestItem;
use App\Observers\ServiceRequestItemObserver;
use App\Models\CashFlow;
use App\Observers\CashFlowObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ServiceRequestItem::observe(ServiceRequestItemObserver::class);
        CashFlow::observe(CashFlowObserver::class);
    }
}
