<?php

namespace App\Repositories;

use App\Models\Team;
use Illuminate\Support\Facades\DB;

class TeamRepository extends BaseRepository
{
    protected function getModel(): string
    {
        return Team::class;
    }

    public function search($query, $column, $data)
    {
        return match ($column) {
            'parent_team_id' => $query->where($column, '=', $data),
            'is_main_team' => $data ? $query->whereNull('parent_team_id') : $query->whereNotNull('parent_team_id'),
            'name','details' => $query->where($column, 'like', "%${data}%"),
            'is_sub_team' => $data ? $query->whereNotNull('parent_team_id') : $query->whereNull('parent_team_id'),
            default => $query,
        };
    }

    public function deleteUsersTeam ($id)
    {
        DB::table('users_team')->where('team_id', $id)->delete();
    }

    public function countMemberOfTeam ($id)
    {
        $countMembers = DB::table('users_team')->where('team_id', $id)->count();
        return $countMembers;
    }
}
