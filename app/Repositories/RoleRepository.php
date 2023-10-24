<?php

namespace App\Repositories;

use App\Models\Role;

class RoleRepository extends BaseRepository
{
    protected function getModel(): string
    {
        return Role::class;
    }

    public function search($query, $column, $data)
    {
        return match ($column) {
            'role_name' => $query->where($column, 'like', "%${data}%"),
            default => $query,
        };
    }
}
