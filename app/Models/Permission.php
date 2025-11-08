<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $primaryKey = 'permission_id';
    protected $fillable = ['permission_key', 'description'];

    public function positions()
    {
        return $this->hasMany(PositionPermission::class, 'permission_id', 'permission_id');
    }
}
