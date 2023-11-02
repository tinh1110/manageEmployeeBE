<?php

namespace App\Http\Controllers\Api;

use App\Common\CommonConst;
use App\Exports\UserTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportUserRequest;
use App\Http\Resources\ImportedUser\ImportedUserResources;
use App\Http\Resources\ProfileResource;
use App\Jobs\importUser;
use App\Models\Imported_users;
use App\Repositories\ImportedUserRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends BaseApiController
{
    public function __construct(
        protected ImportedUserRepository $importedUserRepository,
        protected UserRepository $userRepository
    ) {
    }

    public function importView(Request $request)
    {
        $condition = $request->all();
        $importeds = $this->importedUserRepository->getByCondition($condition, ['created_by']);
        $result = ImportedUserResources::collection($importeds);
        return $this->sendPaginationResponse($importeds, $result);
    }

    public function foo()
    {
//        dd(1243);
        return 123;
    }

    public function getAdmin()
    {
        $condition['role'] = CommonConst::ROLE_ADMIN;
        $users = $this->userRepository->getByCondition($condition);
        $result = ProfileResource::collection($users);
        return $this->sendPaginationResponse($users, $result);
    }

    public function exportTemplate()
    {
        return Excel::download(new UserTemplateExport([]), 'template_import_users.xlsx',
            \Maatwebsite\Excel\Excel::XLSX);
    }

    public function import(ImportUserRequest $request)
    {
        $file = $request->file('file');
        $fileName = date("Y/m/d").'/'.time().'_'.$file->getClientOriginalName();
        $file->storeAs('import_users', $fileName);
        $row_success = 0;
        $row_fail = 0;
        $error = "";
        $id = Auth::user()->id;
        $imported = $this->importedUserRepository->create([
            'created_by_id' => Auth::user()->id,
            'file_name' => $fileName,
            'status' => 0,
            'success_amount' => $row_success,
            'fail_amount' => $row_fail,
            'error' => $error
        ]);
        dispatch(new ImportUser($fileName, $id));
        $result = ImportedUserResources::make($imported);
        return $this->sendResponse($result, __('common.import_done'));

    }
}
