<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_team_id',
        'name',
        'leader_id',
        'details',
        'created_by_id',
        'updated_by_id',
        'deleted_by_id',
    ];

    public function users()
    {
        return $this->belongsToMany('App\Models\User', 'users_team', 'team_id', 'user_id');
    }

    public function getLeader()
    {
        return $this->hasOne('App\Models\User', 'id', 'leader_id');
    }
    public array $sortable = ['created_at', 'name'];
}
