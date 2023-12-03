<?php

namespace App\Http\Resources\Attendance;

use App\Common\CommonConst;
use App\Helpers\DateHelper;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\CustomUserResource;

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

//
//        $startDate = $this->start_date;
//        $endDate = $this->end_date;
//        $startTime =  $this->start_time;
//        $endTime =  $this->end_time;
//
//        $startDateTime = Carbon::parse($startDate . ' ' . $startTime);
//        $endDateTime = Carbon::parse($endDate . ' ' . $endTime);
//
//        if($endDateTime->minute < $startDateTime->minute){
//            $totalTime = -1 + ceil(($endDateTime->minute +60 - $startDateTime->minute)/15)*15/60;
//        }else if($endDateTime->minute === $startDateTime->minute){
//            $totalTime = 0;            }
//        else{
//            $totalTime = -1 + ceil(($endDateTime->minute - $startDateTime->minute)/15)*15/60;
//        }
//        $currentDateTime = $startDateTime;
//
//        while ($currentDateTime <= $endDateTime) {
//            $currentDayOfWeek = $currentDateTime->dayOfWeek;
//            if ($currentDayOfWeek >= Carbon::MONDAY && $currentDayOfWeek <= Carbon::FRIDAY &&
//                (($currentDateTime->hour >= 8 && $currentDateTime->hour < 12) ||($currentDateTime->hour >= 13 && $currentDateTime->hour < 17))) {
//                $totalTime += 1; // Đếm là 1 giờ
//            }
//            $currentDateTime->addHour();
//        }

        $type_name = $this->type->name;
        $managers = CustomUserResource::collection($this->manager);
        $manage_type = $this->manager_type->role_type ?? null;
        if($this->status == CommonConst::ATTENDANCE_ACCEPT) {
            $colors = CommonConst::ATTENDANCE_ACCEPT_COLOR;
        }
        else if ($this->status == CommonConst::ATTENDANCE_REJECT) {
            $colors = CommonConst::ATTENDANCE_REJECT_COLOR;
        }
        else {
            $colors = "";
        }
        if ($this->status == CommonConst::NOT_REVIEWED) {
            $statusString = "NOT REVIEWED";
        }else if ($this->status == CommonConst::ATTENDANCE_REJECT) {
            $statusString = " ATTENDANCE REJECT";
        }else $statusString = " ATTENDANCE ACCEPT";
        if ($this->approver_id) $approverName = $this->approver->name;
        else $approverName = '';
            return [
            'id' => $this->id,
            'color' => $colors,
            'created_by_id' => $this->created_by_id,
            'created_by_name' => $this->user->name,
            'created_role' => $this->user->role->role_name,
            'type_id' => $this->type_id,
            'title' => $this->user->name . ": " . $type_name,
            'type_name' => $type_name,
            'start' => $this->start_date . " " . $this->start_time,
            'end' => $this->end_date." ".$this->end_time,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'total_hours' => $this->total_hours,
            'reason' => $this->reason,
            'img' => $this->img,
            'status' => $this->status,
            'statusString' => $this->statusString,
            'approver_id' => $this->approver_id,
            'approver_name' => $approverName,
            'approved_at' => $this->approved_at,
            'result' => $this->result,
            'managers' => $managers,
            'manage_type' => $manage_type,
            'created_at' => Carbon::parse($this->created_at)->format('Y/m/d H:i:s'),

        ];
    }
}
