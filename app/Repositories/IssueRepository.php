<?php

namespace App\Repositories;

use App\Models\Issue;

class IssueRepository extends BaseRepository
{

    protected function getModel(): string
    {
        return Issue::class;
    }
}
