<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\User\UserResource;
use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseApiController
{
    public function __construct(protected RoleRepository $roleRepository)
    {
    }
    public function login(LoginRequest $request)
    {
        // Get data valid from request
        $credentials = $request->validated();
        // check auth credentials
        if (Auth::attempt($credentials)) {
            $user = auth()->user();
            $token = $user->createToken('authToken');
            // set default expiration
            if (!$request->remember) {
                $token->accessToken->expires_at = now()->addHour(config('sanctum.default_hour_expiration'));
                $token->accessToken->save();
            }
            $permission = $this->roleRepository->findOrFail($user->role->id)->role_permissions;
            $result = ProfileResource::make($user);
            $arr = [
                'access_token' => $token->plainTextToken,
                'user' => $result,
                'permission' => $permission
            ];

            return $this->sendResponse($arr, __('common.request_successful'));
        }
        return $this->sendError( __('common.login_failed'));
    }

    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();
        return $this->sendResponse(null, __('common.logout_successful'));
    }
}
