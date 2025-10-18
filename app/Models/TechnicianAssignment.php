<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicianAssignment extends Model
{
    use HasFactory;

    protected $table = 'technician_assignments';
    protected $primaryKey = 'assignment_id';

    protected $fillable = [
        'item_id',
        'technician_id',
        'role',
        'status',
    ];

    /**
     * 🔗 Relationship: Assigned technician
     */
    public function technician()
    {
        return $this->belongsTo(Employeeprofiles::class, 'technician_id', 'employeeprofiles_id');
    }

    /**
     * 🔗 Relationship: Related service request item
     */
    public function item()
    {
        return $this->belongsTo(ServiceRequestItem::class, 'item_id', 'item_id');
    }
}
