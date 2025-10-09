<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirconServicePrice extends Model
{
    protected $primaryKey = 'aircon_service_price_id';
    protected $fillable = [
        'services_id',
        'aircon_type_id',
        'price',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'services_id');
    }

    public function airconType(): BelongsTo
    {
        return $this->belongsTo(AirconType::class, 'aircon_type_id');
    }
}
