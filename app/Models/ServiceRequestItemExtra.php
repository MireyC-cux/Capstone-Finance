<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequestItemExtra extends Model
{
    protected $table = 'service_request_item_extras';
    protected $primaryKey = 'id';

    protected $fillable = [
        'item_id',
        'name',
        'qty',
        'price',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Relationship: Each extra belongs to one service request item.
     */
    public function serviceRequestItem()
    {
        return $this->belongsTo(ServiceRequestItem::class, 'item_id', 'item_id');
    }
}
