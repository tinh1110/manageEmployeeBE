<?php

namespace App\Http\Controllers\Api;

use App\Common\CommonConst;
use App\Helpers\CommonHelper;
use App\Helpers\FileHelper;
use App\Http\Requests\Issue\CreateIssueRequest;
use App\Http\Requests\Issue\UpdateIssueRequest;
use App\Http\Resources\Issue\IssueNoChildrenResource;
use App\Http\Resources\Issue\IssueResource;
use App\Models\Issue;
use App\Repositories\IssueRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IssueController extends BaseApiController
{

    /**
     * @param IssueRepository $issueRepository
     */
    public function __construct(protected IssueRepository $issueRepository)
    {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index($id, Request $request): JsonResponse
    {
        $condition = $request->all();
        $condition['project_id'] = $id;
//        $condition['type_issue'] = 3;
        $issue = $this->issueRepository->getByCondition($condition, ['created_by', 'assignee']);
        $result = IssueNoChildrenResource::collection($issue);
        return $this->sendResponse($result);
    }

    public function childrenIssue($project_id, $issue_id):JsonResponse
    {
        $condition['project_id'] = $project_id;
        $condition['parent_id'] = $issue_id;
        $issue = $this->issueRepository->getByCondition($condition, ['created_by', 'assignee']);
        $result = IssueResource::collection($issue);
        return $this->sendPaginationResponse($issue, $result);
    }
    public function parent($project_id):JsonResponse
    {
        $condition['project_id'] = $project_id;
        $condition['type_issue'] = 2;
        $issue = $this->issueRepository->getByCondition($condition, ['created_by', 'assignee']);
        $result = IssueResource::collection($issue);
        return $this->sendPaginationResponse($issue, $result);
    }

    /**
     * @param CreateIssueRequest $request
     * @return JsonResponse
     */
    public function store(CreateIssueRequest $request): JsonResponse
    {
        $user_id = $request->user()->id;
        $data = $request->validated();
        if (!in_array('assignee_id', $data)){
            if ($user_id == 1) {
                $data['assignee_id'] =
                    DB::table('users_team')->where('team_id', $data['project_id'])->where('position_id', 1)->first()->user_id;
            } else {
            $data['assignee_id'] = $user_id;
            }
        }
        if ($request->hasFile('image')) {
            $imgName = [];
            foreach ($request->image as $img) {
                $imgPath =pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME).'-'.  time().'.'.$img->extension();
                $folder = CommonConst::ISSUE_IMG_PATH;
                FileHelper::saveFileToStorage($folder, $img, $imgPath);
                $imgName[] = $folder.'/'.$imgPath;
            }
            $data['image'] = $imgName;

        } else {
            $data['image'] =[];
        }
        $data['created_by'] = $user_id;
        $issue = $this->issueRepository->create($data);
        $result = IssueResource::make($issue);
        return $this->sendResponse($result);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function edit($id):JsonResponse
    {
        $issue = $this->issueRepository->findOrFail($id, ['children']);
        $result = IssueResource::make($issue);
        return $this->sendResponse($result);
    }

    /**
     * @param UpdateIssueRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function update(UpdateIssueRequest $request, $id):JsonResponse
    {
        $data = $request->validated();
        if ($data['parent_id'] == $id){
            $data['parent_id'] = null;
        }
        $paths = $this->issueRepository->findOrFail($id)->image ?? [];
        $delete = $request->input('delete') ?? [];

        foreach ($delete as $del) {
            FileHelper::deleteFileFromStorage($del);
        }
        $temp = array_diff($paths,$delete);
        if ($request->hasFile('image')) {
            foreach ($request->image as $img) {
                $imgPath =pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME).'-'.  time().'.'.$img->extension();
                $folder = CommonConst::ISSUE_IMG_PATH;
                FileHelper::saveFileToStorage($folder, $img, $imgPath);
                $temp[] = $folder.'/'.$imgPath;
            }
        }
        $data['image'] = $temp;
        $data['updated_by'] = $request->user()->id;
        $issue = $this->issueRepository->update($id, $data);
        CommonHelper::updatePercentDone($issue->project_id);
        $result = IssueResource::make($issue);
        return $this->sendResponse($result);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function delete($id):JsonResponse
    {
        $project_id = $this->issueRepository->findOrFail($id)->project_id;
        $issue = $this->issueRepository->delete($id);
        $result = IssueResource::make($issue);
//        CommonHelper::updatePercentDone($project_id);
        return $this->sendResponse($result);
    }
}
