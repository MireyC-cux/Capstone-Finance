<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $table = 'employeeprofiles';
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
        return $this->hasOne(Salary::class, 'employeeprofiles_id');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'employeeprofiles_id');
    }

    public function cashAdvances()
    {
        return $this->hasMany(CashAdvance::class, 'employeeprofiles_id');
    }

    public function deductions()
    {
        return $this->hasMany(Deduction::class, 'employeeprofiles_id');
    }

    public function salaryRates()
    {
        return $this->hasMany(EmployeeSalaryRate::class, 'employeeprofiles_id');
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
        return $this->hasMany(Expense::class, 'employeeprofiles_id');
    }
}
