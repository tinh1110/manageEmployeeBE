<?php

namespace App\Http\Resources\Comment;

use App\Http\Resources\ProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResoure extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     * @throws \Exception
     */
    public function toArray(Request $request): array
    {
        $array = [];
        if ($this->children)
            foreach ($this->children as $item) {
                $array[] = CommentResoure::make($item);
            }
        $user = ProfileResource::make($this->user);
        return [
            'comId' => $this->id,
            'userId' => $user->id,
            'fullName' => $user->name,
            'avatarUrl' => $user->avatar ? asset('storage/'. $user->avatar) : "./user.png",
            'text' => $this->body,
            'replies' => $array,
        ];
    }
}
