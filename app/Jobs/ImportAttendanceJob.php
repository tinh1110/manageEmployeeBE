<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Models\RoleAttendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;
use App\Common\CommonConst;


class ImportAttendanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private mixed $rows;
    protected int $id;
    /**
     * Create a new job instance.
     */
    public function __construct($rows,$id)
    {
        $this->rows = $rows;
        $this->id = $id;
    }

    public function rules(): array
    {
        return [
            'type_id' => 'required|exists:attendance_types,id',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s',
            'reason' => 'nullable|string',
            'img' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status' => 'required|integer',
            'result' => 'nullable|string',
            'ids' => ['nullable', 'string', function ($attribute, $value, $fail) {
                try {
                    $ids = explode(",", $value);
                    $check = DB::table('users')
                        ->whereIn('id', $ids)
                        ->count();
                    if ($check !== sizeof($ids)) {
                        $fail('Please check the ids field again!');
                    }
                } catch (Throwable $e) {
                    report($e);
                    $fail('Please check the ids field again!');
                }
            }
            ],
            'approved_at' => 'nullable|date_format:Y-m-d H:i:s',
            'approver_id' => 'nullable|integer|exists:users,id',
        ];
    }


    public function updateTableImportattendances($id, $successAmount, $failAmount, $error): void
    {
        $errorCurrent = DB::table('imported_attendances')->where('id', $id)->select('error', 'total')->get();
        $errorCurrent = $errorCurrent[0]->error;
        $totalRowData = $errorCurrent[0]->total;
        if ($error === "") {
            $errorNew = $errorCurrent;
        } else {
            $errorNew = $errorCurrent . '' . $error;
        }
        if ($failAmount + $successAmount == $totalRowData) {
            if ($failAmount == $totalRowData && $successAmount == 0) {
                $status = CommonConst::STATUS_FAIL;
            } else {
                $status = CommonConst::STATUS_SUCCESS;
            }
            DB::table('imported_attendances')
                ->where('id', $id)
                ->update([
                    'fail_amount' => $failAmount,
                    'success_amount' => $successAmount,
                    'error' => $errorNew,
                    'status' => $status,
                ]);
        } else {
            DB::table('imported_attendances')
                ->where('id', $id)
                ->update([
                    'fail_amount' => $failAmount,
                    'success_amount' => $successAmount,
                    'error' => $errorNew,
                ]);
        }
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $idInTableImport_Attendances = $this->id;
        $infoImportAttendance = DB::table('imported_attendances')->where('id', $idInTableImport_Attendances)->select('success_amount','fail_amount','created_by_id')->get();
        $successAmount = $infoImportAttendance[0]->success_amount;
        $failAmount = $infoImportAttendance[0]->fail_amount;
        $created_by_id = $infoImportAttendance[0]->created_by_id;
        $errorMessages = [];
        foreach ($this->rows as $row) {
            $rowCheck = $row->toArray();
            $validator = Validator::make($rowCheck, $this->rules());
            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $messages) {
                    $isTouch = isset($rowCheck['stt']);
                    if ($isTouch) {
                        $rowNumber = $rowCheck['stt'];
                    } else {
                        $rowNumber = 1;
                    }
                    $errorMessages[$rowNumber][] = [$messages[0]];
                }
                $failAmount += 1;
            } else {
                $atendance = Attendance::create([
                    'type_id' => $row['type_id'],
                    'start_date' => $row['start_date'],
                    'end_date' => $row['end_date'],
                    'start_time' => $row['start_time'],
                    'end_time' => $row['end_time'],
                    'reason' => $row['reason'],
                    'img' => $row['img'],
                    'status' => $row['status'],
                    'result' => $row['result'],
                    'approver_id' => $row['approver_id'],
                    'approved_at' => $row['approved_at'],
                    'created_by_id' => $created_by_id,
                ]);
                $id = $atendance->id;
                $userIds = explode(",", $row['ids']);
                $type_id = is_null($row['approver_id']) ? CommonConst::STATUS_DECLINE : CommonConst::STATUS_APPROVE;
                $arr = [];
                foreach ($userIds as $userId) {
                    $arr[] = [
                        'user_id' => $userId,
                        'attendance_id' => $id,
                        'role_type' => $type_id
                    ];
                }
                RoleAttendance::insert($arr);
                $successAmount += 1;
            }
        }
        if (sizeof($errorMessages) == 0) {
            $errorMessages = "";
        } else {
            $errorMessages = json_encode($errorMessages);
        }
        $this->updateTableImportattendances($idInTableImport_Attendances, $successAmount, $failAmount, $errorMessages);
    }

}

