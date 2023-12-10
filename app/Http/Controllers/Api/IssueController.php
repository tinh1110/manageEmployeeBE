<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Issue\CreateIssueRequest;
use App\Http\Resources\Issue\IssueResource;
use App\Models\Issue;
use App\Repositories\IssueRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $result = IssueResource::collection($issue);
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

    /**
     * @param CreateIssueRequest $request
     * @return JsonResponse
     */
    public function store(CreateIssueRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user_id = $request->user()->id;
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
     * @param CreateIssueRequest $request
     * @param $id
     * @return JsonResponse
     */
    public function update(CreateIssueRequest $request, $id):JsonResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $issue = $this->issueRepository->update($id, $data);

        $result = IssueResource::make($issue);
        return $this->sendResponse($result);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function delete($id):JsonResponse
    {
        $issue = $this->issueRepository->delete($id);
        $result = IssueResource::make($issue);
        return $this->sendResponse($result);
    }
}
