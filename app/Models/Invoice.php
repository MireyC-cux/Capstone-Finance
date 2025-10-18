<?php
// app/Models/Invoice.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model {
    protected $primaryKey = 'invoice_id';
    protected $guarded = [];
    public function billing(){ return $this->belongsTo(Billing::class, 'billing_id'); }
    public function accountsReceivable(){ return $this->belongsTo(AccountsReceivable::class, 'ar_id'); }
}
