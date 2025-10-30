<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArchiveProfile extends Model
{
    use HasFactory;

    protected $table = 'archiveprofiles';
    protected $primaryKey = 'archiveprofiles_id';

    protected $fillable = [
     
        'first_name',
        'last_name',
        'address',
        'email',
        'position',
        'date_of_birth',
        'contact_number',
        'hire_date',
        'status',
        'emergency_contact',
        'card_Idnumber',
        'reason',
        'archived_by',
        'archived_at',
    ];
}
