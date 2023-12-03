<?php

namespace App\Http\Resources\User;

use App\Models\UserTeam;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserTeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $position_id = UserTeam::where('user_id', $this->id)->where('team_id',$this->team_id)->first()->position_id;
        switch ($position_id) {
            case 1:
                $position = "Project manager";
                break;
            case 2:
                $position = "Developer";
                break;
            case 3:
                $position = "Tester";
                break;
            case 4:
                $position = "Comtor";
                break;
            default:
                $position = "Khác";
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar ? asset('storage/'. $this->avatar) : "",
            'address' => $this->address,
            'phone_number' => $this->phone_number,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'status' => $this->status,
            'details' => $this->details,
            'role' => $this->role->role_name,
            'position' => $position
        ];
    }
}
