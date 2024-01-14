<?php

namespace App\Http\Controllers\Api;
use App\Events\EventComment;
use App\Http\Requests\CommentIssue\CreateIssueCommentRequest;
use App\Http\Requests\CommentIssue\UpdateCommentIssueRequest;
use App\Http\Resources\CommentIssue\CommentIssueResource;
use App\Models\Comment;
use App\Models\CommentIssue;
use App\Repositories\CommentIssueRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;

class CommentIssueController extends BaseApiController
{
    public function __construct(protected CommentIssueRepository $commentRepository)
    {
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $condition = $request->all();

        $comments = $this->commentRepository->getByCondition($condition);
        $result = CommentIssueResource::collection($comments);

        return $this->sendResponse($result, __('common.get_data_success'));
    }

    public function getIssueComment(Request $request,$id): \Illuminate\Http\JsonResponse
    {
//        $condition['event_id'] = $id;
        $comments = CommentIssue::where('issue_id', $id)
            ->with('children')
            ->whereNull('parent_id')
        ->get();
//        $comments = $this->commentRepository->getByCondition($condition,['']);
//        $comments = EventComment::where('event_id',$id)->whereNull('parent_id')->get();
        $result = CommentIssueResource::collection($comments);
//        dd($comments[0]->children);
        return $this->sendResponse($result, __('common.get_data_success'));
    }
    public function getChildrenComment(Request $request,$id,$parent_id): \Illuminate\Http\JsonResponse
    {

        $comments = CommentIssue::where('issue_id',$id)->get();
        $result = CommentIssueResource::collection($comments);

        return $this->sendResponse($result, __('common.get_data_success'));
    }

    public function delete($id): \Illuminate\Http\JsonResponse
    {
        $comment = $this->commentRepository->findOrFail($id)->first();

        $comments = $this->commentRepository->delete($id);
        if ($comments) {
            return $this->sendResponse(null, __('common.deleted'));
        }
        return $this->sendError(__('common.not_found'), null, Response::HTTP_NOT_FOUND);
    }

    public function store(CreateIssueCommentRequest $request)
    {
        $data = $request->validated();
        $comment = $this->commentRepository->create($data);
        $result = CommentIssueResource::make($comment);
        return $this->sendResponse($result);
    }

    public function edit(string $id): \Illuminate\Http\JsonResponse
    {
        $event = $this->commentRepository->findOrFail($id, ['user']);
        $result = CommentIssueResource::make($event);

        return $this->sendResponse($result);
    }

    public function update(UpdateCommentIssueRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $comments = $this->commentRepository->update($id, $data);
        $result = CommentIssueResource::make($comments);
        return $this->sendResponse($result);
    }
}
