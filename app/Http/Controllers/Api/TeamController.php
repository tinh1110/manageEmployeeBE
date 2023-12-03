<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Team\DeleteTeamRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\Team\TeamResource;
use App\Http\Requests\Team\CreateTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Requests\Team\RemoveMemberRequest;
use App\Http\Requests\Team\AddMemberRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserTeamResource;
use App\Models\Team;
use App\Models\User;
use App\Models\UserTeam;
use App\Repositories\TeamRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;


class TeamController extends BaseApiController
{
    public function __construct(protected TeamRepository $teamRepository, protected UserRepository $userRepository)
    {
    }

    /*
     * Get list main teams
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $conditions = $request->all();
        $teams = $this->teamRepository->getByCondition($conditions, ['getLeader']);
        $result = TeamResource::collection($teams);
        return $this->sendPaginationResponse($teams, $result);
    }

    /*
     * Create new team
     * */
    public function createNewTeam(CreateTeamRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $data['created_by_id'] = auth()->user()->id;
        $team = $this->teamRepository->create($data);
        $result = TeamResource::make($team);
        return $this->sendResponse($result);
    }

    /*
     * Get list sub team
     * */
//    public function getListSubTeam($id, Request $request): \Illuminate\Http\JsonResponse
//    {
//        $conditions = $request->all();
//        $conditions = array_merge($conditions, ["parent_team_id" => $id]);
//        $teams = $this->teamRepository->getByCondition($conditions, ['getLeader']);
//        $result = TeamResource::collection($teams);
//        return $this->sendPaginationResponse($teams, $result);
//    }

    /*
     * Edit
     */
    public function updateTeam($id, UpdateTeamRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $data['updated_by_id'] = auth()->user()->id;
        $user = $this->teamRepository->update($id, $data);
        $result = TeamResource::make($user);
        return $this->sendResponse($result);
    }

    /*
     * Get detail team
     * */
    public function getDetailTeam($id): \Illuminate\Http\JsonResponse
    {
        $team = $this->teamRepository->findOrFail($id, ['getLeader']);
        $result = TeamResource::make($team);
        return $this->sendResponse($result);
    }


    /*
     * Get list user of team
     */
    public function getListUserOfTeam($id, Request $request): \Illuminate\Http\JsonResponse
    {
        $conditions = $request->all();
        $conditions = array_merge($conditions, ['team_id' => $id,]);
        $users = $this->userRepository->getByCondition($conditions,['role']);
        $users->map(function($user) use ($id) {
            $user->team_id = $id;
            return $user;
        });
        $result = UserTeamResource::collection($users);
        return $this->sendPaginationResponse($users, $result);
    }

    /*
     * Add member to the team
     */
    public function addMember($id, AddMemberRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $team = $this->teamRepository->findOrFail($id, ['getLeader']);
        DB::table('users_team')->insert(['user_id' => $data['id'], 'team_id' => $id, 'position_id' => $data['position_id']]);
        $result = TeamResource::make($team);
        return $this->sendResponse($result);
    }

    /*
     * Remove member from the team
     */
    public function delete($id, RemoveMemberRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $team = $this->teamRepository->findOrFail($id, ['getLeader']);
        DB::table('users_team')->where('team_id', $id)->where('user_id', $data['id'])->delete();
        $result = TeamResource::make($team);
        return $this->sendResponse($result);
    }

    /*
     * Delete one team
     */
    public function deleteTeam($id): \Illuminate\Http\JsonResponse
    {
        $team = $this->teamRepository->findOrFail($id, ['getLeader']);
        DB::table('users_team')->where('team_id', $id)->delete();
        $team->delete();
        return $this->sendResponse(null,"Delete successfully");
    }

    /*
     * get all sub team co phan trang
     */
//    public function allListSubTeam(Request $request): \Illuminate\Http\JsonResponse
//    {
//        $conditions = $request->all();
//        $conditions = array_merge($conditions, ["is_sub_team" => true]);
//        $teams = $this->teamRepository->getByCondition($conditions, ['getLeader']);
//        $result = TeamResource::collection($teams);
//        return $this->sendPaginationResponse($teams, $result);
//    }

    /*
     * get all main team khong co phan trang
     */
    public function getAllMainTeam(Request $request): \Illuminate\Http\JsonResponse
    {
        $conditions =  $request->all();
        $teams = $this->teamRepository->findByCondition($conditions, ['getLeader']);
        $result = TeamResource::collection($teams->get());
        return $this->sendResponse($result);
    }
}
