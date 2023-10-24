<?php

namespace App\Repositories;

use App\Models\Imported_users;

class ImportedUserRepository extends BaseRepository
{
    protected function getModel(): string
    {
        return Imported_users::class;
    }
    public function search($query, $column, $data)
    {
        return match ($column) {
            'file_name' => $query->where($column, 'like', "%${data}%"),
            'status','created_by_id' => $query->where($column, $data),
            'start_time' => $query->whereDate('created_at', '>=', $data),
            'end_time'=>$query->whereDate('created_at', '<=', $data),


            default => $query,
        };
    }
}



