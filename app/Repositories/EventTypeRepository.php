<?php

namespace App\Repositories;

use App\Models\EventType;

class EventTypeRepository extends BaseRepository
{
    protected function getModel(): string
    {
        return EventType::class;
    }
}
