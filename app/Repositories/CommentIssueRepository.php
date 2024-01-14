<?php

namespace App\Repositories;

use App\Models\CommentIssue;

class CommentIssueRepository extends BaseRepository
{

    protected function getModel()
    {
        return CommentIssue::class;
    }
}
