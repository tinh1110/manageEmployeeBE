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
        'time',
        'late',
        'forget',
        'paid_leave',
        'unpaid_leave',
        'day_off',
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
