<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TechnicianAssignment;
use App\Models\Expenses;

class EmployeeProfiles extends Model
{
    use HasFactory;

    protected $primaryKey = 'employeeprofiles_id';
    protected $fillable = [
        'first_name',
        'last_name',
        'address',
        'position',
        'contact_info',
        'hire_date',
        'status',
        'emergency_contact',
        'fingerprint_data'
    ];

    protected $dates = ['hire_date'];

    public function administrativeAccount()
    {
        return $this->hasOne(AdministrativeAccount::class, 'employeeprofiles_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employeeprofiles_id');
    }

    public function leaveOvertimeRequests()
    {
        return $this->hasMany(LeaveOvertimeRequest::class, 'employeeprofiles_id');
    }

    public function salary()
    {
        return $this->hasOne(Salaries::class, 'employeeprofiles_id');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'employeeprofiles_id');
    }

    public function assignedServiceRequestItems()
    {
        return $this->hasMany(ServiceRequestItem::class, 'assigned_technician_id');
    }

    public function technicianAssignments()
    {
        return $this->hasMany(TechnicianAssignment::class, 'employeeprofiles_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expenses::class, 'employeeprofiles_id');
    }
}
