<?php

namespace App\Http\Resources\Event;

use App\Http\Resources\ProfileResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EventResource extends JsonResource
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
        if ($this->image)
        foreach ($this->image as $img) {
            $array[] = asset('storage/'.$img);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'description' => $this->description,
            'location' => $this->location,
            'image' => $array,
            'link' => $this->link,
            'created_at' => date("d-m-Y, H:i:s", $this->created_at->timestamp),
            'created_by' => ProfileResource::make($this->created_by),
        ];
    }
}
