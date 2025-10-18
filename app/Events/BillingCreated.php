<?php
// app/Events/BillingCreated.php
namespace App\Events;
use App\Models\Billing;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BillingCreated {
    use Dispatchable, SerializesModels;
    public $billing;
    public function __construct(Billing $billing){ $this->billing = $billing; }
}
