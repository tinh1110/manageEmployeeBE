<?php

namespace App\Http\Resources\Issue;

use App\Http\Resources\ProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IssueResource  extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignee' => ProfileResource::make($this->assignee_id),
            'project_id' => $this->project_id,
            'subject' => $this->subject,
            'parent_id' => $this->parent_id,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'priority' => $this->priority,
            'status' => $this->status,
            'updated_at' =>  date("d-m-Y", $this->updated_at->timestamp),
            'created_at' => date("d-m-Y", $this->created_at->timestamp),
            'created_by' =>  ProfileResource::make($this->created_by),
            'updated_by' =>  ProfileResource::make($this->updated_by),
        ];
    }
}
