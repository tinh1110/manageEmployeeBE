<?php

namespace App\Imports;

use App\Jobs\ImportAttendanceJob;
use App\Models\Attendance;
use App\Models\ImportAttendances;
use App\Models\Role;
use App\Models\RoleAttendance;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RemembersChunkOffset;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\BeforeImport;
use App\Common\CommonConst;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceImport implements  ToCollection,WithStartRow,WithStyles
{
    use SkipsErrors, SkipsFailures, Importable,RemembersRowNumber,RemembersChunkOffset;
    protected $fileName;
    protected $id;
    protected $imported_id;

    public function __construct($fileName,$id,$imported_id)
    {
        $this->fileName = $fileName;
        $this->id = $id;
        $this->imported_id = $imported_id;
    }
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function collection(Collection $rows)
    {
        $row_success = 0;
        $row_fail = 0;
        $error = 'Không có bản ghi nào trong file';
        $dem = 2;
        $this->writeDataToColumn9($this->fileName,1,'Trạng thái import');
        foreach ($rows as $row) {
            $mappedRow = [];
            foreach ($this->nameToString as $key => $value) {
                $mappedRow[$value] = $row[$key];
            }
            $isValid = $this->validate($mappedRow);
            if ($isValid == "1"){
                $atendance = Attendance::create([
                    'type_id' => $mappedRow['type_id'],
                    'start_date' => $mappedRow['start_date'],
                    'end_date' => $mappedRow['end_date'],
                    'start_time' => $mappedRow['start_time'],
                    'end_time' => $mappedRow['end_time'],
                    'reason' => $mappedRow['reason'],
                    'status' => $mappedRow['status'],
                    'result' => $mappedRow['result'],
                    'approver_id' => $mappedRow['approver_id'],
                    'approved_at' => $mappedRow['approved_at'],
                    'created_by_id' => $mappedRow['user_id'],
                ]);
            $id = $atendance->id;
            $userIds = explode(",", $mappedRow['ids']);
            $type_id = CommonConst::STATUS_APPROVE;
            $arr = [];
            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if ($user){
                    $arr[] = [
                    'user_id' => $userId,
                    'attendance_id' => $id,
                    'role_type' => $type_id
                ];
                }
            }
                RoleAttendance::insert($arr);
                $this->writeDataToColumn9($this->fileName, $dem++, 'import thành công');
                $row_success += 1;
            } else {
                $string = implode(', ', $isValid);
                $row_fail += 1;
                $this->writeDataToColumn9($this->fileName, $dem++, $string);
                $error = 'Dữ liệu lỗi';
            }
            if ($row_success > 0 && $row_fail === 0) {
                $error = null;
            }
        }
        ImportAttendances::where('file_name',$this->fileName)->update([
            'error' => $error,
            'status' => 1,
            'success_amount' => $row_success,
            'fail_amount' => $row_fail,
        ]);
        $imported = ImportAttendances::where('file_name',$this->fileName)->first();
        return redirect()->back();


        /*
         *  $idInTableImport_Attendances = $this->id;
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
         */
    }

    protected $nameToString = [
        0 => 'user_id',
        1 => 'type_id',
        2 => 'start_date',
        3 => 'end_date',
        4 => 'start_time',
        5 => 'end_time',
        6 => 'reason',
        7 => 'status',
        8 => 'result',
        9 => 'ids',
        10 => 'approver_id',
        11 => 'approved_at',

    ];
    protected $names = array(
        'user_id' => 0,
        'type_id' => 1,
        'start_date' => 2,
        'end_date' => 3,
        'start_time' => 4,
        'end_time' => 5,
        'reason' => 6,
        'status' => 7,
        'result' => 8,
        'ids' => 9,
        'approver_id' => 10,
        'approved_at' => 11,

    );

    public function batchSize(): int
    {
        return CommonConst::BATCH_SIZE_ATTENDANCE;
    }

    public function chunkSize(): int
    {
        return CommonConst::CHUNK_SIZE_ATTENDANCE;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('M1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFA500');

    }

    public static function writeDataToColumn9(string $filePath1,int $row,string $error)
    {
        // Lấy đường dẫn đến file Excel
        $filePath = storage_path('app/import_attendance/'. $filePath1);

        // Đọc dữ liệu từ file Excel vào đối tượng Spreadsheet
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Lấy số lượng dòng trong file Excel
        $rowCount = $worksheet->getHighestDataRow();
        $worksheet->getStyle('M1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');

        // Ghi dữ liệu vào cột 9 của tất cả các dòng
        $worksheet->setCellValue('M' . $row, $error);

        // Lưu lại file Excel
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);
    }

    public static function validate($row){
        $validRoles = Role::pluck('id')->toArray();
        $rolesString = implode(',', $validRoles);

        $validator = Validator::make($row, [
            'type_id' => 'required|exists:attendance_types,id',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => 'required|date_format:H:i:s',
            'reason' => 'nullable|string',
            'status' => 'required|integer',
            'result' => 'nullable|string',
            'ids' => 'nullable',
            'user_id' => 'required|integer',
            'approved_at' => 'nullable|date_format:Y-m-d H:i:s',
            'approver_id' => 'nullable|integer|exists:users,id',
        ], [
            'type_id.required' => 'Vui lòng nhập type',
            'type_id.exists' => 'Vui lòng nhập type phù hợp',
            'start_date.date_format' => 'Ngày bắt đầu không đúng định dạng',
            'start_date.required' => 'Ngày bắt đầu không được bỏ trống',
            'end_date.date_format' => 'Ngày kết thúc không đúng định dạng',
            'end_date.required' => 'Ngày kết thúc không được bỏ trống',
            'start_time.date_format' => 'Giờ bắt đầu không đúng định dạng',
            'start_time.required' => 'Giờ bắt đầu không được bỏ trống',
            'end_time.date_format' => 'Giờ kết thúc không đúng định dạng',
            'end_time.required' => 'Giờ kết thúc không được bỏ trống',
            'reason.string' => 'Lí do phải daạng chuỗi',
            'status.required' => 'Trạng thái không được bỏ trống',
            'status.integer' => 'Trạng thái không đúng định dạng',
            'user_id.required' => 'Id user không được bỏ trống',
            'user_id.integer' => 'Id user không đúng định dạng',
        ]);

        if ($validator->fails()) {
            return $validator->messages()->all();
        }
        return "1";

    }
}





