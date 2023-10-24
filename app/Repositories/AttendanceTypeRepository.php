<?php

namespace App\Repositories;

use App\Models\AttendanceType;

class AttendanceTypeRepository extends BaseRepository
{
    protected function getModel(): string
    {
        return AttendanceType::class;
    }
}
