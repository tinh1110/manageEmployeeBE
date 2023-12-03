<?php

namespace App\Http\Controllers\Api;
use App\Events\EventComment;
use App\Http\Requests\Comment\CreateCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\Comment\CommentResoure;
use App\Models\Comment;
use App\Repositories\CommentRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends BaseApiController
{
    public function __construct(protected CommentRepository $commentRepository)
    {
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $condition = $request->all();

        $comments = $this->commentRepository->getByCondition($condition,['parent']);
        $result = CommentResoure::collection($comments);

        return $this->sendResponse($result, __('common.get_data_success'));
    }

    public function getEventComment(Request $request,$id): \Illuminate\Http\JsonResponse
    {
//        $condition['event_id'] = $id;
        $comments = Comment::where('event_id', $id)
        ->with('children')
        ->whereNull('parent_id')
        ->get();
//        $comments = $this->commentRepository->getByCondition($condition,['']);
//        $comments = EventComment::where('event_id',$id)->whereNull('parent_id')->get();
        $result = CommentResoure::collection($comments);
//        dd($comments[0]->children);
        return $this->sendResponse($result, __('common.get_data_success'));
    }
    public function getChildrenComment(Request $request,$id,$parent_id): \Illuminate\Http\JsonResponse
    {

        $comments = Comment::where('event_id',$id)->where('parent_id',$parent_id)->get();
        $result = CommentResoure::collection($comments);

        return $this->sendResponse($result, __('common.get_data_success'));
    }

    public function delete($id): \Illuminate\Http\JsonResponse
    {
        $comment = $this->commentRepository->findOrFail($id)->first();

        $comments = $this->commentRepository->delete($id);
        broadcast(new EventComment($comment));

        if ($comments) {
            return $this->sendResponse(null, __('common.deleted'));
        }
        return $this->sendError(__('common.not_found'), null, Response::HTTP_NOT_FOUND);
    }

    public function store(CreateCommentRequest $request)
    {
        $data = $request->validated();
        $comment = $this->commentRepository->create($data);
        $result = CommentResoure::make($comment);
        Event::dispatch(new EventComment($comment));
        return $this->sendResponse($result);
    }

    public function edit(string $id): \Illuminate\Http\JsonResponse
    {
        $event = $this->commentRepository->findOrFail($id, ['user']);
        $result = CommentResoure::make($event);

        return $this->sendResponse($result);
    }

    public function update(UpdateCommentRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $comments = $this->commentRepository->update($id, $data);
        $result = CommentResoure::make($comments);
        broadcast(new EventComment($comments));
        return $this->sendResponse($result);
    }
}
