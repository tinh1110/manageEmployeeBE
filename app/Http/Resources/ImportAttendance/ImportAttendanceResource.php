<?php

namespace App\Http\Resources\ImportAttendance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\CustomUserResource;

class ImportAttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

        public function toArray(Request $request): array
        {
            return [
                'file_name' => $this->file_name,
                'id' => $this->id,
                'created_by_id' => $this->created_by_id,
                'status' => $this->status,
                'success_amount' => $this->success_amount,
                'fail_amount' => $this->fail_amount,
                'error' => $this->error,
                'created_at' => $this->created_at->format("Y-m-d H:i:s"),
                'total' => $this->total,
            ];
        }
}
