<?php

namespace App\Http\Resources\Team;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return int
     */
    public function countUsersOfTeam($id): int
    {
        $totalMember = 0;
        //Kiểm tra có phải team cha không
        $IsParrent = DB::table('teams')->where('parent_team_id', $id)->count();
//        dd($IsParrent);
        if ($IsParrent === 0) {
            //Đếm member của team con
            $totalMember=DB::table('users_team')->where('team_id',$id)->count();
        } else {
            // lấy ra tất cả team con của team cha
            $allSubTeam = DB::table('teams')->select('id')->where('parent_team_id', $id);
            //Đếm member của các team con đó
            $totalMember=DB::table('users_team')
                ->whereIn('team_id', $allSubTeam)
                ->distinct('user_id')
                ->count();
        }
        return $totalMember;
    }

    public function countSubTeamsOfTeam($id): int
    {
        $totalSub = DB::table('teams')->where('parent_team_id', $id)->count();
        return $totalSub;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_team_id' => $this->parent_team_id,
            'name' => $this->name,
            'leader' => UserResource::make($this->getLeader),
            'details' => $this->details,
            'created_at' => $this->created_at->format("Y-m-d H:i:s"),
            'total_member' => $this->countUsersOfTeam($this->id),
            'total_subteam' => $this->countSubTeamsOfTeam($this->id),
        ];
    }
}
