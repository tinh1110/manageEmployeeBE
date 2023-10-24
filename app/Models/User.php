<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Role;
use App\Models\Attendance;
use App\Models\RoleAttendance;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected const Male = 1;
    protected const Female = 2;
    protected const Active = 0;
    protected const Inactive = 1;


    public function role()
    {
        return $this->hasOne(Role::class, 'id', 'role_id');
    }


    /**
     * Get the user gender
     */
    public function getGender($value)
    {
        switch ($value) {
            case self::Male:
                return 'Nam';
            case self::Female:
                return 'Nữ';
            default:
                return 'Khác';
        }
    }


    /**
     * Get the user status
     */
    public function getStatus($status)
    {
        if ($status == self::Active) {
            return 'Còn hoạt động';
        }
        if ($status == self::Inactive) {
            return 'Không còn hoạt động';
        }
        return 'Khác';
    }

    /**
     * Get all attendances of an user
     */
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'created_by_id', 'id');
    }

    /**
     * Get the attendance that list in the role_attendances table
     */
    public function attendance_manager()
    {
        return $this->belongsToMany(Attendance::class, 'role_attendances', 'user_id', 'attendance_id');
    }

    /**
     * Get the attendance manage type (only view or can review that attendacne)
     */
    public function attendance_manager_type()
    {
        return $this->hasOne(RoleAttendance::class, 'user_id', 'id');
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */


    public function team()
    {
        return $this->belongsToMany('App\Models\Team', 'users_team', 'user_id', 'team_id');
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'address',
        'phone_number',
        'dob',
        'details',
        'gender',
        'role_id',
        'status',
        'created_by_id',
        'updated_by_id',
        'deleted_by_id'
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

    // Setup which column will take when query user model. Take all column if not set
//    protected array $selectable = ['*'];

    // Setup which column use to order
    public array $sortable = ['name'];
}
