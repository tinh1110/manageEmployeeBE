<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\User;
use App\Models\AttendanceType;
use App\Models\RoleAttendance;

class Attendance extends Model
{
    use HasFactory;

    const STATUS_APPROVED = 1;
    const STATUS_REJECT = 2;
    const STATUS_PENDING = 0;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'created_by_id',
        'type_id',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'reason',
        'img',
        'status',
        'result',
        'total_hours',
        'approver_id',
        'approved_at'
    ];



    public function user()
    {
        return $this->belongsTo(User::class, 'created_by_id', 'id');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(AttendanceType::class, 'type_id', 'id');
    }

    public function manager()
    {
        return $this->belongsToMany(User::class, 'role_attendances', 'attendance_id', 'user_id');
    }

    /**
     * Get the attendance manage type (only view or can review that attendacne)
     */
    public function manager_type()
    {
        return $this->hasOne(RoleAttendance::class, 'attendance_id', 'id');
    }
}
