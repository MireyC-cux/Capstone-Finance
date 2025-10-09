<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the employee profile associated with the user.
     */
    public function employeeProfile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class, 'user_id');
    }

    /**
     * Get the administrative account associated with the user.
     */
    public function administrativeAccount(): HasOne
    {
        return $this->hasOne(AdministrativeAccount::class, 'user_id');
    }

    /**
     * Check if the user has an administrative role.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->administrativeAccount !== null;
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->name;
    }
}
