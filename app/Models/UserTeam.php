<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
