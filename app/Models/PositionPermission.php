<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PositionPermission extends Model
{
    protected $fillable = ['position', 'permission_id', 'is_allowed'];

    public function permission()
    {
        return $this->belongsTo(Permission::class, 'permission_id', 'permission_id');
    }
}
