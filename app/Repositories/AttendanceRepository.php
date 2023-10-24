<?php

namespace App\Repositories;

use App\Models\Attendance;
use App\Helpers\CommonHelper;

class AttendanceRepository extends BaseRepository
{
    protected function getModel(): string
    {
        return Attendance::class;
    }

    public function search($query, $column, $data)
    {
        return match ($column) {
            'status' => $query->where($column, $data),
            'created_by_id' => $query->where($column, $data),
            'manager'=> $query->whereRelation('manager', 'user_id', $data),
            'start' => $query->where('start_date', '<=', $data),
            'end' => $query->where('end_date', '>=', $data),
            default => $query,
        };
    }

    public function getAttendanceByCondition($condition, $relations = [], $relationCounts = []) {
        $condition = CommonHelper::removeNullValue($condition);
        $data = collect($condition);

        // select list column
        $entities = $this->findByCondition($condition, $relations, $relationCounts);
        // order list
        $orderBy = $data->has('sort') && in_array($data['sort'], $this->model->sortable) ? $data['sort'] : $this->model->getKeyName();
        $entities = $entities->orderBy($orderBy, $data->has('sortType') && $data['sortType'] == 1 ? 'asc' : 'desc');

        return $entities->get();
    }
}
