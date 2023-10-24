<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\User;

class Event extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public function created_by()
    {
        return $this->hasOne(User::class, 'id', 'created_by_id');
    }

    protected $fillable = [
        'name',
        'type',
        'start_time',
        'end_time',
        'description',
        'location',
        'image',
        'status',
        'link',
        'created_by_id',
    ];

    protected array $sortable = ['start_time'];
    protected $casts = [
        'image' => 'array',
    ];
}
