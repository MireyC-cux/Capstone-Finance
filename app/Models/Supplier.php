<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $primaryKey = 'supplier_id';
    protected $fillable = [
        'supplier_name',
        'contact_info',
        'address',
        'email'
    ];

    public function accountsPayable()
    {
        return $this->hasMany(AccountsPayable::class, 'supplier_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'supplier_id');
    }
}
