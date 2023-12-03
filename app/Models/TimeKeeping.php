<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeKeeping extends Model
{
    use HasFactory;

    protected $fillable = [
        'month',
        'user_id',
        'user_name',
        'time',
        'late',
        'forget',
        'paid_leave',
        'unpaid_leave',
        'day_work',
        'punish',
    ];

    protected $casts = [
        'time' => 'json',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
