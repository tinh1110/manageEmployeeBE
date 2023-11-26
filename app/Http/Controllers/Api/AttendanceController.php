<?php

namespace App\Http\Controllers\Api;

use App\Exports\ExportTeamplateImportAttendance;
use App\Http\Requests\Attendance\CreateAttendanceRequest;
use App\Http\Requests\Attendance\ImportAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Http\Resources\ImportAttendance\ImportAttendanceResource;
use App\Http\Resources\User\UserResource;
use App\Imports\AttendanceImport;
use App\Jobs\ImportAttendanceJob;
use App\Models\ImportAttendances;
use App\Repositories\AttendanceRepository;
use App\Repositories\ImportAttendanceRepository;
use App\Repositories\RoleAttendanceRepository;
use App\Repositories\UserRepository;
use App\Repositories\AttendanceTypeRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Resources\Attendance\AttendanceResource;
use App\Jobs\SendAttendanceReviewMail;
use App\Jobs\NewAttendanceMail;
use App\Common\CommonConst;
use Illuminate\Support\Facades\DB;
use App\Helpers\FileHelper;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExportAttendance;
use App\Http\Resources\User\CustomUserResource;
use App\Http\Resources\Attendance\AttendanceTypeResource;

class AttendanceController extends BaseApiController
{
    public function __construct(
        protected AttendanceRepository       $attendanceRepository,
        protected RoleAttendanceRepository   $roleAttendanceRepository,
        protected UserRepository             $userRepository,
        protected ImportAttendanceRepository $importAttendanceRepository,
        protected AttendanceTypeRepository   $attendanceTypeRepository)
    {
    }

    /**
     * Show all attendances with condition (optional)
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $condition = $request->all();
        // if in manageMode, manager can view attendances that he can review
        // else user can only view his attendance
        if (!empty($request->manageMode)) {
            $condition['manager'] = $user->id;
            $attendance = $this->attendanceRepository->getAttendanceByCondition($condition);
        } else {
            $condition['created_by_id'] = $user->id;
            $attendance = $this->attendanceRepository->getAttendanceByCondition($condition);
        }
        // add users and attendane types to get them in FE
        $users = CustomUserResource::collection($this->userRepository->findAll());
        $attendanceType = AttendanceTypeResource::collection($this->attendanceTypeRepository->findAll());

        $data = AttendanceResource::collection($attendance);
        $result = [
            'data' => $data,
            'users' => $users,
            'attendance_types' => $attendanceType,
        ];

        return $this->sendResponse($result);
    }

    public function all(Request $request): \Illuminate\Http\JsonResponse
    {
        $condition = $request->all();
        $events = $this->attendanceRepository->getByTime($condition, ['user']);
        $result = AttendanceResource::collection($events);

        return $this->sendPaginationResponse($events, $result);
    }

    public function type(Request $request){
        $attendanceType = AttendanceTypeResource::collection($this->attendanceTypeRepository->findAll());
        return $this->sendResponse($attendanceType);
    }
    /**
     * Store an attendance to db
     *
     * @param CreateAttendanceRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateAttendanceRequest $request): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try
        {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');
        $startDateTime = Carbon::parse($startDate . ' ' . $startTime);
        $endDateTime = Carbon::parse($endDate . ' ' . $endTime);

        if($endDateTime->minute < $startDateTime->minute){
            $totalTime = -1 + ceil(($endDateTime->minute +60 - $startDateTime->minute)/15)*15/60;
        }else if($endDateTime->minute === $startDateTime->minute){
            $totalTime = 0;            }
        else{
            $totalTime = -1 + ceil(($endDateTime->minute - $startDateTime->minute)/15)*15/60;
        }
        $currentDateTime = $startDateTime;

        while ($currentDateTime <= $endDateTime) {
            $currentDayOfWeek = $currentDateTime->dayOfWeek;
            if ($currentDayOfWeek >= Carbon::MONDAY && $currentDayOfWeek <= Carbon::FRIDAY &&
                (($currentDateTime->hour >= 8 && $currentDateTime->hour < 12) ||($currentDateTime->hour >= 13 && $currentDateTime->hour < 17))) {
                $totalTime += 1; // Đếm là 1 giờ
            }
            $currentDateTime->addHour();
        }

            $user = auth()->user();
            $hasIMG = false;
            $canSaveIMG = true;
            // Get data valid from request
            $data = $request->validated();
            $data['total_hours'] = $totalTime;
            $data['created_by_id'] = $user->id;
            if ($request->hasFile('img')) {
                $imgName = auth()->user()->name . time() . '.' . $request->img->extension();
                $folder = CommonConst::ATTENDANCE_IMG_PATH;
                $hasIMG = true;
                $data['img'] = $folder . '/' . $imgName;
            }
            $attendance = $this->attendanceRepository->create($data);

            $result = AttendanceResource::make($attendance);
            // add which user can view and review this attendance by id
            // can pass multiple manager id
            if (!empty($request->ids)) {
                $attendance->manager()->attach($request->ids, ['role_type' => CommonConst::CAN_REVIEW]);
                $manager_emails = $this->userRepository->getEmailsOfUsers($request->ids);
                $new_attendance_data = $data;
                $new_attendance_data['name'] = $user->name;
                $new_attendance_data['email'] = $user->email;
                $new_attendance_data['type_name'] = $attendance->type->name;
                NewAttendanceMail::dispatch($manager_emails, $new_attendance_data);
            }
            if ($hasIMG && $canSaveIMG) FileHelper::saveFileToStorage($folder, $request->img, $imgName);
            DB::commit();
            return $this->sendResponse($result);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendExceptionError($e);
        }
    }

    /**
     * Delete attendance by id
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $id): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $attendance = $this->attendanceRepository->findOrFail($id);
            // if the attendance is reviewed, user cannot delete it, user cannot delete others users attendance
            if ($attendance->status != CommonConst::NOT_REVIEWED || $attendance->created_by_id != auth()->user()->id) {
                return $this->sendError("Bạn không có quyền xóa", Response::HTTP_FORBIDDEN, 403);
            }

            // delete the relation in role_attendance table
            $this->roleAttendanceRepository->delete($id);
            $path = $this->attendanceRepository->findOrFail($id)->img;
            if ($path) {
                FileHelper::deleteFileFromStorage($path);
            }
            $attendance = $this->attendanceRepository->delete($id);
            if ($attendance) {
                DB::commit();
                return $this->sendResponse(null, "Xóa thành công");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendExceptionError($e);
        }
    }

    /**
     * Get attendance info by id before update
     *
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        $attendance = $this->attendanceRepository->findOrFail($id);

        $result = AttendanceResource::make($attendance);
        return $this->sendResponse($result);
    }

    /**
     * Update attendance by id
     *
     * @param UpdateAttendanceRequest $request
     * @param string $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateAttendanceRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $startTime = $request->input('start_time');
            $endTime = $request->input('end_time');

            $startDateTime = Carbon::parse($startDate . ' ' . $startTime);
            $endDateTime = Carbon::parse($endDate . ' ' . $endTime);

            if($endDateTime->minute < $startDateTime->minute){
                $totalTime = -1 + ceil(($endDateTime->minute +60 - $startDateTime->minute)/15)*15/60;
            }else if($endDateTime->minute === $startDateTime->minute){
                $totalTime = 0;            }
            else{
                $totalTime = -1 + ceil(($endDateTime->minute - $startDateTime->minute)/15)*15/60;
            }
            $currentDateTime = $startDateTime;

            while ($currentDateTime <= $endDateTime) {
                $currentDayOfWeek = $currentDateTime->dayOfWeek;
                if ($currentDayOfWeek >= Carbon::MONDAY && $currentDayOfWeek <= Carbon::FRIDAY &&
                    (($currentDateTime->hour >= 8 && $currentDateTime->hour < 12) ||($currentDateTime->hour >= 13 && $currentDateTime->hour < 17))) {
                    $totalTime += 1; // Đếm là 1 giờ
                }
                $currentDateTime->addHour();
            }
            $user = auth()->user();
            $hasIMG = false;
            $canSaveIMG = true;
            $canDeleteOldIMG = false;
            $attendance = $this->attendanceRepository->findOrFail($id);
            //User cannot update others users attendance
            if ($attendance->created_by_id != $user->id) {
                return $this->sendError("Bạn không có quyền sửa", Response::HTTP_FORBIDDEN, 403);
            }
            //User cannot update the attendance that has been reviewd
            if ($attendance->status != CommonConst::NOT_REVIEWED) {
                return $this->sendError("Không thể sửa đơn đã duyệt", Response::HTTP_FORBIDDEN, 403);
            }
            // Get data valid from request
            $data = $request->validated();
            $data['total_hours'] = $totalTime;
            $data['updated_by_id'] = $user->id;
            $path = $attendance->img;
            $folder = CommonConst::ATTENDANCE_IMG_PATH;
            if ($request->exists('delete_img')) {
                $canDeleteOldIMG = true;
                $data['img'] = null;
            }
            if ($request->hasFile('img')) {
                $canDeleteOldIMG = true;
                $hasIMG = true;
                $imgName = $user->name . time() . '.' . $request->img->extension();
                $data['img'] = $folder . '/' . $imgName;
            }
            $attendance = $this->attendanceRepository->update($id, $data);

            $result = AttendanceResource::make($attendance);
            // add which user can view and review this attendance by id
            // can pass multiple manager id
            if (!empty($request->ids)) {
                $attendance->manager()->detach();
                $attendance->manager()->attach($request->ids, ['role_type' => CommonConst::CAN_REVIEW]);
            }
            if ($canDeleteOldIMG && $path) FileHelper::deleteFileFromStorage($path);
            if ($hasIMG && $canSaveIMG) FileHelper::saveFileToStorage($folder, $request->img, $imgName);
            DB::commit();
            return $this->sendResponse($result);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendExceptionError($e);
        }
    }

    /**
     * Review an attendance by id (accept or reject)
     *
     * @param string $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function review(Request $request, string $id): \Illuminate\Http\JsonResponse
    {
        $attendance = $this->attendanceRepository->findOrFail($id, ['user']);
        $managers = $attendance->manager;
        $user_id = $request->user()->id;
        foreach ($managers as $manager) {
            if ($manager->id == $user_id) {
                $currentUrl = $request->url();
                $status = (strpos($currentUrl, '/accept/')) ? CommonConst::ATTENDANCE_ACCEPT : CommonConst::ATTENDANCE_REJECT;
                $data = [
                    "status" => $status,
                    "approver_id" => auth()->user()->id,
                    "approved_at" => date('Y-m-d H:i:s'),
                    'name' => auth()->user()->name
                ];
                if (!empty($request->result)) {
                    $data['result'] = $request->result;
                }
                $attendance = $this->attendanceRepository->update($id, $data);

                $result = AttendanceResource::make($attendance);
                $user_email = $attendance->user->email;
                SendAttendanceReviewMail::dispatch($user_email, $data);
                return $this->sendResponse($result);
            }
        }
        // if the manager role_type (in role_attendance table) is only view, cannot review
//        if (empty($manage_type) || !($manage_type->role_type == CommonConst::CAN_REVIEW)) {
            return $this->sendError("Bạn không có quyền duyệt đơn này", Response::HTTP_FORBIDDEN, 403);
//        }

    }

    public function export(Request $request)
    {
        $user = $request->user();
        $condition = $request->all();
        // if in manageMode, manager can view attendances that he can review
        // else user can only view his attendance
        if (!empty($request->manageMode)) {
            $condition['manager'] = $user->id;
            $attendance = $this->attendanceRepository->getAttendanceByCondition($condition);
        } else {
            $condition['created_by_id'] = $user->id;
            $attendance = $this->attendanceRepository->getAttendanceByCondition($condition);
        }
        $result = AttendanceResource::collection($attendance);

        $fileName = date("Y-m-d") . '-' . time() . '-attendances.xlsx';
        return Excel::download(new ExportAttendance($result), $fileName, \Maatwebsite\Excel\Excel::XLSX);
    }
    public function exportAll(Request $request)
    {
        $condition = $request->all();
        $attendance = $this->attendanceRepository->getByTime($condition, ['user']);
        $result = AttendanceResource::collection($attendance);

        $fileName = date("Y-m-d") . '-' . time() . '-attendances.xlsx';
        return Excel::download(new ExportAttendance($result), $fileName, \Maatwebsite\Excel\Excel::XLSX);
    }

    public function exportTemplate()
    {
        return Excel::download(new ExportTeamplateImportAttendance, 'template_attendances.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    /*
     * Import User from file xlsx or csv
     */
    public function importAttendance(ImportAttendanceRequest $request): \Illuminate\Http\JsonResponse
    {
        $file = $request->file('import_file');
        $fileName = date("Y/m/d").'/'.time().'_'.$file->getClientOriginalName();
        $file->storeAs('import_attendance', $fileName);
        $row_success = 0;
        $row_fail = 0;
        $error = "";
        $id = Auth::user()->id;
        $imported = $this->importAttendanceRepository->create([
            'created_by_id' => Auth::user()->id,
            'file_name' => $fileName,
            'status' => 0,
            'success_amount' => $row_success,
            'fail_amount' => $row_fail,
            'error' => $error
        ]);
        ImportAttendanceJob::dispatch($fileName,$id,$imported->id);
        $result = ImportAttendanceResource::make($imported);
        return $this->sendResponse($result, __('common.import_done'));
    }

    public function statisticalFileImport(Request $request)
    {
        $conditions = $request->all();
        $importAttenndances = $this->importAttendanceRepository->getByCondition($conditions);
        $result = ImportAttendanceResource::collection($importAttenndances);
        return $this->sendPaginationResponse($importAttenndances, $result);
    }


}
