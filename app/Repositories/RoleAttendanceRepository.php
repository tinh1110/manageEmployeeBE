<?php

namespace App\Repositories;

use App\Models\RoleAttendance;

class RoleAttendanceRepository extends BaseRepository
{
    protected function getModel(): string
    {
        return RoleAttendance::class;
    }

    public function delete(string $id)
    {
        $result = $this->model::where('attendance_id', $id);
        if ($result) {
            $result->delete();

            return true;
        }

        return false;
    }
}
