<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ServiceRequestItem;
use App\Observers\ServiceRequestItemObserver;
use App\Models\CashFlow;
use App\Observers\CashFlowObserver;
use App\Models\ServiceRequest;
use App\Observers\ServiceRequestObserver;
use App\Models\PurchaseOrder;
use App\Observers\PurchaseOrderObserver;

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
        ServiceRequest::observe(ServiceRequestObserver::class);
        PurchaseOrder::observe(PurchaseOrderObserver::class);
    }

    
}
