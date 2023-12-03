<?php

namespace App\Repositories;

use App\Models\CommentLike;

class CommentLikeRepository extends BaseRepository
{

    protected function getModel()
    {
        return CommentLike::class;
    }
}
