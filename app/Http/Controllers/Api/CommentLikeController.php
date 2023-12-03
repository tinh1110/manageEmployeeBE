<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Comment\CreateCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\Comment\CommentResoure;
use App\Repositories\CommentRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CommentLikeController extends BaseApiController
{
    public function __construct(protected CommentRepository $commentRepository)
    {
    }

    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $condition = $request->all();

        $comments = $this->commentRepository->getByCondition($condition,['user']);
        $result = CommentResoure::collection($comments);

        return $this->sendResponse($result, __('common.get_data_success'));
    }

    public function getEventComment(Request $request,$id): \Illuminate\Http\JsonResponse
    {
        $condition = $request->all();

        $comments = $this->commentRepository->getByCondition($condition,['user']);
        $result = CommentResoure::collection($comments);

        return $this->sendResponse($result, __('common.get_data_success'));
    }

    public function delete($id): \Illuminate\Http\JsonResponse
    {
        $path = $this->commentRepository->findOrFail($id)->image;

        $comments = $this->commentRepository->delete($id);
        if ($comments) {
            return $this->sendResponse(null, __('common.deleted'));
        }
        return $this->sendError(__('common.not_found'), null, Response::HTTP_NOT_FOUND);
    }

    public function store(CreateCommentRequest $request)
    {
        $data = $request->validated();
        $comments = $this->commentRepository->create($data);
        $result = CommentResoure::make($comments);
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
        return $this->sendResponse($result);
    }


}
