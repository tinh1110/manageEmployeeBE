<?php

namespace App\Http\Resources\ImportedUser;

use App\Http\Resources\ProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImportedUserResources extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function toArray(Request $request): array
    {
        if ($this->status ==0){ $result = "Đang import";$status =0;}
        else if ($this->fail_amount == 0 && $this->success_amount>0) {$result = "Import Thành Công"; $status =1;}
        else {$result = "Import Thất bại";$status =2;}

        $string = $this->file_name;
        $parts = explode('/', $string);
        $file_name = end($parts);
        $filename = substr($file_name, strpos($file_name, '_') + 1);
        return [
            'id' => $this->id,
            'result' => $result,
            'file_name' => $filename,
            'link' => asset('storage/import_users/'.$this->file_name),
            'status' => $status,
            'success_amount' => $this->success_amount,
            'fail_amount' => $this->fail_amount,
            'error' => $this->error,
            'updated_at' =>  date("d-m-Y, H:i:s", $this->updated_at->timestamp),
            'created_at' => date("d-m-Y, H:i:s", $this->created_at->timestamp),
            'created_by' =>  ProfileResource::make($this->created_by),
        ];
    }
}
