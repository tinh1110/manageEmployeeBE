<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Team;
use App\Models\User;

class UserTeam extends Model
{
    use HasFactory;
    //set table name
    protected $table = 'users_team';
    protected $fillable = [
        'team_id',
        'user_id',
        'position_id',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id',);
    }
}
