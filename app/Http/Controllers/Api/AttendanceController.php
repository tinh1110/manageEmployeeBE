<?php

namespace App\Http\Controllers\Api;

use App\Exports\ExportTeamplateImportAttendance;
use App\Http\Requests\Attendance\CreateAttendanceRequest;
use App\Http\Requests\Attendance\ImportAttendanceRequest;
use App\Http\Requests\Attendance\UpdateAttendanceRequest;
use App\Http\Resources\ImportAttendance\ImportAttendanceResource;
use App\Http\Resources\User\UserResource;
use App\Imports\AttendanceImport;
use App\Models\ImportAttendances;
use App\Repositories\AttendanceRepository;
use App\Repositories\ImportAttendanceRepository;
use App\Repositories\RoleAttendanceRepository;
use App\Repositories\UserRepository;
use App\Repositories\AttendanceTypeRepository;
use Illuminate\Http\Request;
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
        try {
            $user = auth()->user();
            $hasIMG = false;
            $canSaveIMG = true;
            // Get data valid from request
            $data = $request->validated();
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
                return $this->sendError("Cannot delete this attendance", Response::HTTP_FORBIDDEN, 403);
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
                return $this->sendResponse(null, "Delete attendance successfully");
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
            $user = auth()->user();
            $hasIMG = false;
            $canSaveIMG = true;
            $canDeleteOldIMG = false;
            $attendance = $this->attendanceRepository->findOrFail($id);
            //User cannot update others users attendance
            if ($attendance->created_by_id != $user->id) {
                return $this->sendError("Cannot update this attendance", Response::HTTP_FORBIDDEN, 403);
            }
            //User cannot update the attendance that has been reviewd
            if ($attendance->status != CommonConst::NOT_REVIEWED) {
                return $this->sendError("Cannot update this attendance", Response::HTTP_FORBIDDEN, 403);
            }
            // Get data valid from request
            $data = $request->validated();
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
        $manage_type = $attendance->manager_type;
        // if the manager role_type (in role_attendance table) is only view, cannot review
        if (empty($manage_type) || !($manage_type->role_type == CommonConst::CAN_REVIEW)) {
            return $this->sendError("You cannot process this!", Response::HTTP_FORBIDDEN, 403);
        }
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

    public function exportTemplate()
    {
        return Excel::download(new ExportTeamplateImportAttendance, 'template_attendances.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    /*
     * Import User from file xlsx or csv
     */
    public function importAttendance(ImportAttendanceRequest $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validated();

        $file_name_of_request = $data['import_file']->getClientOriginalName();
        $value = [
            'file_name' => $file_name_of_request,
            'created_by_id' => auth()->user()->id,
            'status' => 0,
            'success_amount' => 0,
            'fail_amount' => 0,
            'error' => "",
        ];
        $importAttendance = $this->importAttendanceRepository->create($value);
        $id = $importAttendance->id;
        $import = new AttendanceImport($id);// Tạo một
        $import->import($data['import_file']);

        return $this->sendResponse(null, 'Upload file import successfully, please check mail!');
    }

    public function statisticalFileImport(Request $request)
    {
        $conditions = $request->all();
        $importAttenndances = $this->importAttendanceRepository->getByCondition($conditions);
        $result = ImportAttendanceResource::collection($importAttenndances);
        return $this->sendPaginationResponse($importAttenndances, $result);
    }


}
