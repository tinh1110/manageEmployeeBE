<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Issue\CreateIssueRequest;
use App\Http\Resources\Issue\IssueResource;
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
    public function index(Request $request): JsonResponse
    {
        $condition = $request->all();
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
        $issue = $this->issueRepository->findOrFail($id);
        $result = IssueResource::make($issue);
        return $this->sendResponse($result);
    }

    public function update(CreateIssueRequest $request, $id):JsonResponse
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $issue = $this->issueRepository->update($id, $data);

        $result = IssueResource::make($issue);
        return $this->sendResponse($result);
    }

    public function delete($id):JsonResponse
    {
        $issue = $this->issueRepository->delete($id);
        $result = IssueResource::make($issue);
        return $this->sendResponse($result);
    }
}
