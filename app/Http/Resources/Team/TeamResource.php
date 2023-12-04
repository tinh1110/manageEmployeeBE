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
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status="";
        $totalMember=DB::table('users_team')->where('team_id',$this->id)->count();
        switch ($this->status){
            case 1:
                $status = "Chưa bắt đầu";
                break;
            case 2:
                $status = "Đang làm";
                break;
            case 3:
                $status = "Bị hủy";
                break;
            case 4:
                $status = "Tạm dừng";
                break;
            case 5:
                $status = "Hoàn thành";
                break;
            default:
                $status="Khác";
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'leader' => UserResource::make($this->getLeader),
            'details' => $this->details,
            'status' => $this->status,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'customer' => $this->customer,
            'created_at' => $this->created_at->format("Y-m-d H:i:s"),
            'total_member' => $totalMember,
        ];
    }
}
