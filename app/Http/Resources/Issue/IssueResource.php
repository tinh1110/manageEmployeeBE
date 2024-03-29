<?php

namespace App\Http\Resources\Issue;

use App\Http\Resources\ProfileResource;
use App\Models\Issue;
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
        foreach ($this->children as $child) {
            $child->assignee = $child->assignee_id ? ProfileResource::make(User::where('id', $child->assignee_id)->first()) : null;
            $child->created_by = $child->created_by ? ProfileResource::make(User::where('id', $child->created_by)->first()) : null;
            $child->updated_by = $child->updated_by ? ProfileResource::make(User::where('id', $child->updated_by)->first()) : null;
        }
        $array = [];
        if ($this->image)
            foreach ($this->image as $img) {
                $array[] = asset('storage/'.$img);
            }
        return [
            'id' => $this->id,
            'assignee' => $user_assignee,
            'assignee_id' => $this->assignee_id,
            'project_id' => $this->project_id,
            'project' => $this->team->name,
            'subject' => $this->subject,
            'parent_id' => $this->parent_id,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'image' => $array,
            'priority' => $this->priority,
            'status' => $this->status,
            'comment' => $this->comment,
            'updated_at' => $this->updated_at ? date("d-m-Y", $this->updated_at->timestamp) : null,
            'created_at' =>  $this->created_at ? date("d-m-Y", $this->created_at->timestamp) : null,
            'created_by' => $user_created,
            'updated_by' =>  $user_updated,
            'children' => ($this->parent_id) ? null : $this->children,
        ];
    }
}
