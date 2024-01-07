<?php

namespace App\Repositories;

use App\Models\Team;
use Carbon\Carbon;
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
            'name','details','customer' => $query->where($column, 'like', "%${data}%"),
            'status', => $query->where($column, $data),
            'start_date' => $query->where('end_date', '>=', Carbon::parse($data)),
            'end_date' => $query->where('start_date', '<=',  Carbon::parse($data)),
            'is_sub_team' => $data ? $query->whereNotNull('parent_team_id') : $query->whereNull('parent_team_id'),
            'user_join' => $this->handlUserJoin($query, $data),
            default => $query,
        };
    }
    private function handlUserJoin($query, $data)
    {
        $query->join('users_team', 'teams.id', '=', 'users_team.team_id')
            ->where('users_team.user_id', $data);
        return $query;
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
