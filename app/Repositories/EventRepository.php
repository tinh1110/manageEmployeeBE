<?php

namespace App\Repositories;

use App\Models\Event;

class EventRepository extends BaseRepository
{

    protected function getModel()
    {
        return Event::class;
    }

    public function search($query, $column, $data)
    {
        return match ($column) {
            'name', 'location' => $query->where($column, 'like', "%${data}%"),
            'type' => $query->where($column, $data),
            'date' => $query->where('start_time', '<=', $data)->where('end_time', '>=', $data),
            default => $query,
        };
    }
}
