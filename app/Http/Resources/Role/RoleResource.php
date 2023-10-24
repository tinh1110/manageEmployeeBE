<?php

namespace App\Http\Resources\Role;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'role_name' => $this->role_name,
            'role_permissions' => $this->role_permissions,
            'description' => $this->description,
            'created_at' => $this->created_at,
        ];
    }
}
