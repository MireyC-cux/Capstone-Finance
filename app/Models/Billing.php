<?php
// app/Models/Billing.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Billing extends Model {
    protected $primaryKey = 'billing_id';
    protected $guarded = [];
    protected $casts = ['meta'=>'array'];

    public function serviceRequest(){ return $this->belongsTo(ServiceRequest::class, 'service_request_id'); }
    public function customer(){ return $this->belongsTo(Customer::class, 'customer_id'); }
    public function invoice(){ return $this->hasOne(Invoice::class, 'billing_id'); }
}
