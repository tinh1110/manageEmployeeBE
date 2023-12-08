<?php

namespace App\Http\Resources\Issue;

use App\Http\Resources\ProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IssueResource  extends JsonResource
{

    public function toArray(Request $request): array
    {
        $user_created = $this->created_by ? ProfileResource::make(User::where('id', $this->created_by)->first()) : null;
        $user_assignee = $this->assignee_id ? ProfileResource::make(User::where('id', $this->assignee_id)->first()) : null;
        $user_updated = $this->updated_by ?  ProfileResource::make(User::where('id', $this->updated_by)->first()) : null;
        return [
            'id' => $this->id,
            'assignee' => $user_assignee,
            'project_id' => $this->project_id,
            'subject' => $this->subject,
            'parent_id' => $this->parent_id,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'priority' => $this->priority,
            'status' => $this->status,
            'updated_at' => $this->updated_at ? date("d-m-Y", $this->updated_at->timestamp) : null,
            'created_at' =>  $this->created_at ? date("d-m-Y", $this->created_at->timestamp) : null,
            'created_by' => $user_created,
            'updated_by' =>  $user_updated,
            'children' =>  $this->children,
        ];
    }
}
