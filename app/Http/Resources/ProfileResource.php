<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

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
            'role' => $this->role->role_name
        ];
    }
}
