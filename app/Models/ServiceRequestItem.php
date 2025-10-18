<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestItem extends Model
{
    public $incrementing = true;
    protected $primaryKey = 'item_id';
    protected $fillable = [
        'service_request_id',
        'services_id',
        'aircon_type_id',
        'service_type',
        'unit_type',
        'quantity',
        'unit_price',
        'discount',
        'tax',
        'line_total',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'assigned_technician_id',
        'status',
        'bill_separately',
        'billed',
        'service_notes'
    ];
    
    /**
     * Get the service associated with the service request item.
     */
    public function service()
    {
        return $this->belongsTo(Service::class, 'services_id', 'services_id');
    }

    protected $dates = [
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'line_total' => 'decimal:2',
        'bill_separately' => 'boolean',
        'billed' => 'boolean',
        'status' => 'string'
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function airconType()
    {
        return $this->belongsTo(AirconType::class, 'aircon_type_id');
    }

    public function assignedTechnician()
    {
        return $this->belongsTo(EmployeeProfile::class, 'assigned_technician_id');
    }
}
