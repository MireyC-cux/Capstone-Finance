<?php
// app/Models/AccountsReceivable.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class AccountsReceivable extends Model {
    protected $table = 'accounts_receivable';
    protected $primaryKey = 'ar_id';
    protected $guarded = [];
    protected $appends = ['balance'];

    public function getBalanceAttribute(){
        return round($this->total_amount - $this->amount_paid, 2);
    }

    public function payments(){ return $this->hasMany(PaymentReceived::class, 'ar_id'); }

    public function customer(){ return $this->belongsTo(Customer::class, 'customer_id'); }

    public function invoice(){ return $this->belongsTo(Invoice::class, 'invoice_id'); }

    public function adjustments(){ return $this->hasMany(ARAdjustment::class, 'ar_id'); }
}
