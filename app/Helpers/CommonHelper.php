<?php

namespace App\Helpers;

use App\Common\ResourceConst;
use App\Models\Issue;
use App\Models\Team;
use Symfony\Component\HttpFoundation\Request;

class CommonHelper
{
    /**
     * @param Request $request
     *
     * @return string
     */
    public static function getDataFromHeaderRequest(Request $request, $key): string
    {
        return $request->headers->get($key);
    }


    public static function removeNullValue($data): array
    {
        return array_filter($data, fn($value) => !is_null($value));
    }

    public static function updatePercentDone($project_id)
    {
        $issue_done = count(Issue::where('project_id', $project_id)->where('status', 4)->whereNull('parent_id')->get());
        $issue = count(Issue::where('project_id', $project_id)->whereNull('parent_id')->get());
        $percent = round($issue_done / $issue *100);
        Team::where('id', $project_id)->update(['percent_done' => $percent]);
    }
}
