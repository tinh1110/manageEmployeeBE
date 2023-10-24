<?php

namespace App\Http\Controllers\Api;

use App\Common\CommonConst;
use App\Exports\UsersExport;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\ImportUserRequest;
use App\Http\Resources\User\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Exports\UsersExportTemplate;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;
use App\Helpers\FileHelper;
use Illuminate\Support\Facades\DB;

class UserController extends BaseApiController
{
    public function __construct(protected UserRepository $userRepository)
    {
    }

    /**
     * Show all users with condition (optional)
     *
     * @param  Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $condition = $request->all();
        $users = $this->userRepository->getByCondition($condition);
        $result = UserResource::collection($users);

        return $this->sendPaginationResponse($users, $result);
    }

    /**
     * API lay ra tat ca user khong phan trang
     */
    public function getAll(): \Illuminate\Http\JsonResponse
    {
        $users = $this->userRepository->findAll();
        $result = UserResource::collection($users);
        return $this->sendResponse($result);
    }


    /**
     * Store an user to db
     *
     * @param  CreateUserRequest  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateUserRequest $request): \Illuminate\Http\JsonResponse
    {
        // Get data valid from request
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $avatarName = time().'.'.$request->avatar->extension();
            $folder = CommonConst::USER_AVATAR_PATH;
            FileHelper::saveFileToStorage($folder, $request->avatar, $avatarName);
            $data['avatar'] = $folder.'/'.$avatarName;
        }
        $data['created_by_id'] = auth()->user()->id;
        $data['password'] = Hash::make($data['password']);
        $user = $this->userRepository->create($data);

        $result = UserResource::make($user);
        return $this->sendResponse($result);
    }

    /**
     * Delete user by id
     *
     * @param  string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $id): \Illuminate\Http\JsonResponse
    {
        if (auth()->user()->id == $id) {
            return $this->sendError("Can't delete yourself", Response::HTTP_FORBIDDEN, 403);
        }
        $user = $this->userRepository->delete($id);

        if ($user) {
            return $this->sendResponse(null, "Delete user successfully");
        }
        return $this->sendError("User not found", Response::HTTP_NOT_FOUND, 404);
    }

    /**
     * Delete user permanently by id
     *
     * @param  string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete(string $id): \Illuminate\Http\JsonResponse
    {
        if (auth()->user()->id == $id) {
            return $this->sendError("Can't delete yourself", Response::HTTP_FORBIDDEN, 403);
        }
        $user =  $this->userRepository->forceDelete($id);

        if ($user) {
            return $this->sendResponse(null, "Delete user successfully");
        }

        return $this->sendError("User not found", Response::HTTP_NOT_FOUND, 404);
    }

    /**
     * Get user info by id before update
     *
     * @param  string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        $users = $this->userRepository->findOrFail($id);
        $result = UserResource::make($users);

        return $this->sendResponse($result);
    }

    /**
     * Update user by id
     *
     * @param  UpdateUserRequest  $request
     * @param  string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        // Get data valid from request
        $data = $request->validated();
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        // path of user avatar (if exist)
        $path = $this->userRepository->findOrFail($id)->avatar;
        // if user click x button in his avatar, delete that avatar
        if ($request->exists('delete_avatar')) {
            if ($path) {
                FileHelper::deleteFileFromStorage($path);
            }
            $data['avatar'] = null;
        }
        // if an user has avatar, delete that avatar from storage and replace it with new avatar
        if ($request->hasFile('avatar')) {
            if ($path) {
                FileHelper::deleteFileFromStorage($path);
            }
            $avatarName = time().'.'.$request->avatar->extension();
            $folder = CommonConst::USER_AVATAR_PATH;
            FileHelper::saveFileToStorage($folder, $request->avatar, $avatarName);
            $data['avatar'] = $folder.'/'.$avatarName;
        }
        $data['updated_by_id'] = auth()->user()->id;
        $user = $this->userRepository->update($id, $data);

        $result = UserResource::make($user);
        return $this->sendResponse($result);
    }

    /**
     * Export template file
     *
     */
    public function exportTemplate()
    {
        return Excel::download(new UsersExportTemplate, 'users.xlsx');
        // return $this->sendResponse(null, 'Export successfully');
    }

    /**
     * Import users with file (xlsx, csv)
     *
     * @param  ImportUserRequest  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function importUser(ImportUserRequest $request): \Illuminate\Http\JsonResponse
    {
        $file = $request->file('import_file');
        $data = $request->validated();
        $spreadsheet = Excel::toArray(new UsersImport, $file);
        $totalRows = count($spreadsheet[0]);

        $file_name = $data['import_file']->getClientOriginalName();
        $user_login = $request->user()->id;
        $import_id = ImportUser::create([
            'created_by_id' => $user_login,
            'file_name' => $file_name,
            'status' => CommonConst::PROCESSING,
            'success_amount' => 0,
            'fail_amount' => 0,
            'error' => "",
            'total' => $totalRows,
        ]);
        session()->put('import_id', $import_id->id);
        $import = new UsersImport($import_id->id);
        $import->import($data['import_file']);
        if (!empty($import->failures())) {
            $errorMessages = [];
            foreach ($import->failures() as $failure) {
                $row = $failure->row(); // row that went wrong
                $row_failure = $failure->errors()[0]; // Actual error messages from Laravel validator
                $errorMessages[$row][] = $row_failure;
            }
        }
        ImportUser::find(session()->get('import_id'))->update([
            'error' => $errorMessages, 'fail_amount' => count($errorMessages)
        ]);
        return $this->sendResponse('The update processing is complete ');
    }

    public function importInfor(Request $request)
    {
        $user = $request->user();
        $import_infor = ImportUser::where("created_by_id", $user->id)->orderBy('created_at', 'DESC')->get();
        return $this->sendResponse($import_infor, 'Import information of user');
    }

    /**
     * Export users by condition
     *
     * @param  Request  $request
     *
     */
    public function exportUser(Request $request)
    {
        $condition = $request->all();

        $users = $this->userRepository->getByCondition($condition);
        $result = UserResource::collection($users);
        $export = new UsersExport($result);
        return $export->download('users.xlsx');
    }

    /**
     * Delete multiple users by id
     *
     * @param  Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMulti(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $users_ids = $request->selected_user;
            if (in_array(auth()->user()->id, $users_ids)) {
                return $this->sendError("Can't delete yourself", Response::HTTP_FORBIDDEN, 403);
            }
            $this->userRepository->deleteMulti($users_ids);

            DB::commit();
            return $this->sendResponse(null, "Delete users successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendExceptionError($e);
        }
    }

    /**
     * Force delete multiple users by id
     *
     * @param  Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDeleteMulti(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $users_ids = $request->selected_user;
            $this->userRepository->forceDeleteMulti($users_ids);

            DB::commit();
            return $this->sendResponse(null, "Delete users successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendExceptionError($e);
        }
    }

    /**
     * Restore users by id
     *
     * @param  string  $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore(string $id): \Illuminate\Http\JsonResponse
    {
        $user = $this->userRepository->restore($id);

        if ($user) {
            return $this->sendResponse(null, "Restore user successfully");
        }

        return $this->sendError("User not found", Response::HTTP_NOT_FOUND, 404);
    }

    /**
     * Restore multiple users by id
     *
     * @param  Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function restoreMulti(Request $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $users_ids = $request->selected_user;
            $this->userRepository->restoreMulti($users_ids);

            DB::commit();
            return $this->sendResponse(null, "Restore users successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendExceptionError($e);
        }
    }
}
