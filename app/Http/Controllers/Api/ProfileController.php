<?php

namespace App\Http\Controllers\Api;

use App\Common\CommonConst;
use App\Helpers\FileHelper;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\EditProfileRequest;
use App\Http\Requests\User\EditUserRequest;
use App\Http\Resources\ProfileResource;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class ProfileController extends BaseApiController
{
    public function __construct(protected UserRepository $userRepository)
    {
    }

    public function profile()
    {
        $user = $this->userRepository->findOrFail(auth()->user()->id, ['role']);
        $result = ProfileResource::make($user);
        return $this->sendResponse($result);
    }

    public function user($id)
    {
        $user = $this->userRepository->findOrFail($id, ['role']);
        $result = ProfileResource::make($user);
        return $this->sendResponse($result);
    }

    public function updateProfile(EditProfileRequest $request)
    {
        // Get data valid from request
        $id = auth()->user()->id;
        $data = $request->validated();
//
//        if (empty($data['password'])) {
//            unset($data['password']);
//        }
        $path = $this->userRepository->findOrFail($id)->avatar;
        if ($request['delete_avt'] && $path) {
            $data['avatar'] = null;
            FileHelper::deleteFileFromStorage($path);
        } else {

            if ($request->hasFile('avatar')) {
                if ($path) {
                    FileHelper::deleteFileFromStorage($path);
                }
                $imgPath = time().'.'.$request->avatar->extension();
                $folder = CommonConst::USER_AVATAR_PATH;
                FileHelper::saveFileToStorage($folder, $request->avatar, $imgPath);
                $data['avatar'] = $folder.'/'.$imgPath;
            } else {
                $data['avatar'] = $path;
            }
        }
        $user = $this->userRepository->update($id, $data);
        $result = ProfileResource::make($user);
        return $this->sendResponse($result, __('common.updated'));
    }

    public function changePassword(ChangePasswordRequest $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ( Hash::check($user->password,$data['old_password']) ){
            return $this->sendError('Mật khẩu cũ không đúng');
        }
        $user = $this->userRepository->update($user->id, $data);
        $result = ProfileResource::make($user);
        return $this->sendResponse($result, __('common.updated'));
    }

}
