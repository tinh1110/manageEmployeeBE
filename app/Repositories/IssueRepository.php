<?php

namespace App\Repositories;

use App\Models\Issue;

class IssueRepository extends BaseRepository
{
    /**
     * @return string
     */
    protected function getModel(): string
    {
        return Issue::class;
    }

    public function search($query, $column, $data)
    {
        return match ($column) {
            'subject' => $query->where($column, 'like', "%${data}%"),
            'assignee_id', 'project_id' => $query->where($column, $data),
            'type_issue' => $this->handleTypeIssue($query, $data),
            'status' => $this->handleStatus($query, $data),
            'date' => $query->where('start_time', '<=', $data)->where('end_time', '>=', $data),
            default => $query,
        };
    }

    private function handleTypeIssue($query, $data)
    {
        if ($data == 2) {
            // Thực hiện các xử lý cụ thể cho trường hợp 'type_issue'
            // Ví dụ: $query->where('type_issue', $data)
            $query->whereNull('parent_id');
        }
        else if ($data == 3){
            $query->whereNotNull('parent_id');
        }
        return $query;
    }
    private function handleStatus($query, $data)
    {
        return match ($data) {
            1,2,3,4,5 => $query->where('status', $data),
            6 => $query->where('status', '!=', 5),
            default => $query,

        };
    }
}
