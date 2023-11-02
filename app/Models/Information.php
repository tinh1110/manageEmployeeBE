<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    use HasFactory;
    protected $fillable = [
        'role_name',
        'description',
        'created_by_id',
        'update_by_id',
        'deleted_by_id',
        'role_permissions',
    ];

}
