<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceOrderChange extends Model
{
    protected $table = 'service_order_changes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'service_request_id',
        'item_id',
        'changed_by',
        'field',
        'old_value',
        'new_value',
        'reason',
    ];

    protected $casts = [
        'service_request_id' => 'integer',
        'item_id' => 'integer',
        'changed_by' => 'integer',
    ];

    /**
     * Relationship: The change belongs to a specific service request.
     */
    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id', 'service_request_id');
    }

    /**
     * Relationship: The change may belong to a specific item within a service request.
     */
    public function serviceRequestItem()
    {
        return $this->belongsTo(ServiceRequestItem::class, 'item_id', 'item_id');
    }

    /**
     * Relationship: The user/admin who made the change.
     */
    public function changedByUser()
    {
        return $this->belongsTo(User::class, 'changed_by', 'id');
    }
}
