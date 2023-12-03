<?php

namespace App\Repositories;

use App\Common\CommonConst;
use App\Models\Attendance;
use App\Helpers\CommonHelper;
use Carbon\Carbon;
use function Sodium\add;

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
            'type_id' => $query->where($column, $data),
            'manager' => $query->whereRelation('manager', 'user_id', $data),
            'start' => $query->where('end_date', '<=', Carbon::parse($data)),
            'end' => $query->where('start_date', '>=',  Carbon::parse($data)),
            'start_date' => $query->where('end_date', '>=', Carbon::parse($data)),
            'end_date' => $query->where('start_date', '<=',  Carbon::parse($data)),
            'ids' => $query->whereIn('created_by_id', $data),
            default => $query,
        };
    }

    public function getByTime($condition, $relations = [], $relationCounts = [])
    {
        $now = Carbon::now();
        $condition = CommonHelper::removeNullValue($condition);
        $data = collect($condition);

        // select list column
        $entities = $this->findByCondition($condition, $relations, $relationCounts);
        // order list
        $orderBy = $data->has('sort') && in_array($data['sort'], $this->model->sortable) ? $data['sort'] : $this->model->getKeyName();
        $entities = $entities->orderBy($orderBy, $data->has('sortType') && $data['sortType'] == 1 ? 'asc' : 'desc');

        $start = null;
        $end = null;
        if ($data->has('time')) {
            switch ($data['time']) {
                case 'today':
                    $start = now()->startOfDay();
                    $end = now()->endOfDay();
                    break;
                case 'week':
                    $start = now()->startOfWeek();
                    $end = now()->endOfWeek();
                    break;
                case 'month':
                    $start = now()->startOfMonth();
                    $end = now()->endOfMonth();
                    break;
                default:
            }
        }
        if ($start) {
            $entities = $entities->where(function ($query) use ($start, $end) {
                $query->where('start_date', '<=', $end)
                    ->where('end_date', '>=', $start);
            });
        }
        $limit = $data->has('limit') ? (int)$data['limit'] : CommonConst::DEFAULT_PER_PAGE;
        if ($limit) {
            return $entities->paginate($limit);
        }
        return $entities->get();
    }

    public function getAttendanceByCondition($condition, $relations = [], $relationCounts = [])
    {
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
