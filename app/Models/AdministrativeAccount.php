<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdministrativeAccount extends Model
{
    protected $primaryKey = 'admin_id';
    protected $fillable = [
        'employeeprofiles_id',
        'admin_position',
        'username',
        'password'
    ];

    protected $hidden = [
        'password'
    ];

    public function employeeProfile()
    {
        return $this->belongsTo(EmployeeProfile::class, 'employeeprofiles_id');
    }
}
