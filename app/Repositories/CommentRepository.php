<?php

namespace App\Repositories;

use App\Models\Comment;

class CommentRepository extends BaseRepository
{
    public function search($query, $column, $data)
    {
        return match ($column) {
            'event_id' => $query->where($column, $data),
            default => $query,
        };
    }

    protected function getModel()
    {
        return Comment::class;
    }
}
