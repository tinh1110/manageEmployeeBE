<?php

namespace App\Http\Resources\Time;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeResource extends JsonResource
{
    public function toArray(Request $request): array
    {

        return [
            'id' => $this->id,
            'month' => $this->month,
            'user_id' => $this->user_id,
            'user_name' => $this->user_name,
            'time' => $this->time,
            'late' => $this->late,
            'forget' => $this->forget,
            'paid_leave' => $this->paid_leave,
            'unpaid_leave' => $this->unpaid_leave,
            'day_work' => $this->day_work,
            'day_off' => $this->user_id ? User::where('id', $this->user_id)->first()->day_off : 0,
        ];
    }
}
