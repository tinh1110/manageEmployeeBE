<?php

namespace App\Repositories;

use App\Models\UserTeam;

class UserTeamRepositiory extends BaseRepository
{
    protected function getModel(): string
    {
        return UserTeam::class;
    }
}
