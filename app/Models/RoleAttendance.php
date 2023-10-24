<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'attendance_id', 'role_type'
    ];

    public $timestamps = false;
}
