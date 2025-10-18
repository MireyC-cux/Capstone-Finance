<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';
    protected $primaryKey = 'id';

    protected $fillable = [
        'event_type',
        'title',
        'context_type',
        'context_id',
        'amount',
        'meta',
    ];

    protected $casts = [
        'amount' => 'float',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
