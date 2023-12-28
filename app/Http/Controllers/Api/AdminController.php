<?php

namespace App\Http\Controllers\Api;

use App\Common\CommonConst;
use App\Exports\ExportTime;
use App\Exports\UserTemplateExport;
use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ImportUserRequest;
use App\Http\Requests\ReportRequest;
use App\Http\Resources\ImportedUser\ImportedUserResources;
use App\Http\Resources\ProfileResource;
use App\Http\Resources\Time\TimeResource;
use App\Imports\TimeKeepingImport;
use App\Jobs\importUser;
use App\Jobs\SendMailReport;
use App\Models\Imported_users;
use App\Models\TimeKeeping;
use App\Models\User;
use App\Repositories\ImportedUserRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Type\Time;

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

    public function importTime(ImportUserRequest $request)
    {
        $file = $request->file('file');
        $fileName = date("Y/m/d").'/'.time().'_'.$file->getClientOriginalName();
        $file->storeAs('timeKeeping', $fileName);
        Excel::import(new TimeKeepingImport(), $file);
        $count = User::whereNull('deleted_at')->count();
        $data = TimeKeeping::orderByDesc('id')->limit($count)->get();
        $sortedData = $data->sortBy('id');
        $month = TimeKeeping::orderByDesc('id')->first()->month;
        $result = [
            'data' => TimeResource::collection($sortedData),
            'month' => $month
        ];
        return $this->sendResponse($result, __('common.import_done'));

    }

    public function timeUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $user_id = $request->user()->id;
        $data = TimeKeeping::where('user_id', $user_id)->orderByDesc('id')->first();
        $month = TimeKeeping::orderByDesc('id')->first()->month;
        $result = [
            'data' => TimeResource::make($data),
            'month' => $month
        ];
        return $this->sendResponse($result, __('common.get_data_success'));
    }

    public function timeList(Request $request): \Illuminate\Http\JsonResponse
    {
        $count = User::whereNull('deleted_at')->count();
        $data = TimeKeeping::orderByDesc('id')->limit($count)->get();
        $sortedData = $data->sortBy('id');
        $month = TimeKeeping::orderByDesc('id')->first()->month;
        $result = [
            'data' => TimeResource::collection($sortedData),
            'month' => $month
            ];
        return $this->sendResponse($result, __('common.get_data_success'));
    }

    public function exportTime(){
        $count = User::whereNull('deleted_at')->count();
        $data = TimeKeeping::orderByDesc('id')->limit(16)->get();
        $month = TimeKeeping::orderByDesc('id')->first()->month;
        $sortedData = $data->sortBy('id');
        $result = TimeResource::collection($sortedData);
        return Excel::download(new ExportTime($result), 'timekeeping:' . $month . '.xlsx',
            \Maatwebsite\Excel\Excel::XLSX);
    }

    public function report(ReportRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->all();
        if ($data['isAnonymous'] == "true") {
            $data['user'] = $request->user()->name;
        } else {
            $data['user'] ="Thành viên ẩn danh";
        }
        $data['image'] = [];
        if ($request->hasFile('image')) {
            $imgName = [];
            foreach ($request->image as $img) {
                $imgPath =pathinfo($img->getClientOriginalName(), PATHINFO_FILENAME).'-'.  time().'.'.$img->extension();
                $folder = "report";
                FileHelper::saveFileToStorage($folder, $img, $imgPath);
                $imgName[] = $folder.'/'.$imgPath;
            }
            $data['image'] = $imgName;

        }
        dispatch(new SendMailReport($data));
        return $this->sendResponse(null, "Gửi góp ý thành công");
    }
}
