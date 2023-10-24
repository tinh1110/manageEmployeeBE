<?php

namespace App\Repositories;

use App\Models\ImportAttendances;

class ImportAttendanceRepository extends BaseRepository
{
    protected function getModel(): string
    {
        return ImportAttendances::class;
    }

    public function search($query, $column, $data)
    {
        return match ($column) {
            'file_name' => $query->where($column, 'like', "%${data}%"),
            'status' => $query->where($column, 'like', "%${data}%"),
            'start_time' => $query->whereDate('created_at', '>=', $data),
            'end_time' => $query->whereDate('created_at', '<=', $data),
            default => $query,
        };
    }
}
