<?php

namespace App\Http\Controllers\Api;

use App\Common\CommonConst;
use App\Http\Requests\Role\CreateRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Requests\Role\DeleteRoleRequest;
use App\Http\Resources\Role\RoleResource;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends BaseApiController
{
    public function __construct(protected RoleRepository $roleRepository,protected  UserRepository $userRepository)
    {
    }

    /*
     * Get list roles
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $condition = $request->all();
        $roles = $this->roleRepository->getByCondition($condition);
        $result = RoleResource::collection($roles);
        return $this->sendPaginationResponse($roles, $result);
    }

    /*
     * create new role
     * */
    public function create(CreateRoleRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $data['created_by_id'] = auth()->user()->id;
        $data['role_permissions'] = config('rolePermission.default_permissions');
        $role = $this->roleRepository->create($data);
        $result = RoleResource::make($role);
        return $this->sendResponse($result);
    }


    /*
     * get detail role
     * */
    public function getDetailRole($id): \Illuminate\Http\JsonResponse
    {
        $role = $this->roleRepository->findOrFail($id);
        $result = RoleResource::make($role);
        return $this->sendResponse($result);
    }

    /*
     * Edit role
     * */
    public function update($id, UpdateRoleRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();
        $data['updated_by_id'] = auth()->user()->id;
        if ($id== CommonConst::ADMIN_ID ) return $this->sendError( __('common.update_admin'));
        $role_permissions = $request->input('role');
        $data['role_permissions'] = $role_permissions;
        $role = $this->roleRepository->update($id, $data);
        $result = RoleResource::make($role);
        return $this->sendResponse($result);
    }

    /*
     * Delete role
     * */
    public function delete($id ): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {

            $role = $this->roleRepository->findOrFail($id);
            $users = $this->userRepository->getByCondition(["role" => $id]);

            if (count($users) == 0) {
                $result = $role->delete();
                if ($result) {
                    DB::commit();
                    return $this->sendResponse(null, __('common.deleted'));
                }
            }
            else {
                DB::rollBack();
                return $this->sendError("Cann't delete role has user", null, Response::HTTP_CONFLICT);
                }
            DB::rollBack();
            return $this->sendError(__('common.deleted'), null, Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendExceptionError($e, Response::HTTP_NOT_FOUND);
        }
    }

    public function listRoute()
    {
        $routeCollection = Route::getRoutes();
        $routeNames = [];
        foreach ($routeCollection as $route) {
            if (str_contains($route->getAction()['prefix'], 'api')) {
                $routeNames[] = $route->getName();
            }
        }
        $routeNames = array_values(array_diff($routeNames, config('rolePermission.role_remove')));
        return $this->sendResponse($routeNames, __('common.get_data_success'));
    }

    public function changePermission(Request $request, $id)
    {

        $role_permissions = $request->input('role');
        $data['role_permissions'] = $role_permissions;
        $role = $this->roleRepository->update($id, $data);
        $result = RoleResource::make($role);
        return $this->sendResponse($result);
    }
}
