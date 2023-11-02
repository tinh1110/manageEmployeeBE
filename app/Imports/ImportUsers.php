<?php

namespace App\Imports;

use App\Events\ImportedUser;
use App\Models\Imported_users;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportUsers implements ToCollection,WithStartRow,WithStyles
{

    protected $fileName;
    protected $id;

    public function __construct($fileName,$id)
    {
        $this->fileName = $fileName;
        $this->id = $id;
    }
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
            $dob = date('Y-m-d', strtotime('1899-12-30 +'.$mappedRow['dob'].' days'));
            $mappedRow['dob'] = $dob;
            $isValid = $this->validate($mappedRow);
            if ($isValid == "1") {
                User::create([
                    'name' => $mappedRow['name'],
                    'email' => $mappedRow['email'],
                    'password' => $mappedRow['password'],
                    'address' => $mappedRow['address'],
                    'dob' => $mappedRow['dob'],
                    'phone_number' => $mappedRow['phone_number'],
                    'gender' => $mappedRow['gender'],
                    'role_id' => $mappedRow['role'],
                    'status' => $mappedRow['status'],
                    'details' => $mappedRow['details'],
                    'created_by_id' => $this->id
                ]);
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
        Imported_users::where('file_name',$this->fileName)->update([
            'error' => $error,
            'status' => 1,
            'success_amount' => $row_success,
            'fail_amount' => $row_fail,
        ]);
        $imported = Imported_users::where('file_name',$this->fileName)->first();
        broadcast(new ImportedUser($imported));
        return redirect()->back();
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('I1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFA500');
    }
    protected $nameToString = [
        0 => 'name',
        1 => 'email',
        2 => 'password',
        3 => 'address',
        4 => 'dob',
        5 => 'phone_number',
        6 => 'gender',
        7 => 'role',
        8 => 'status',
        9 => 'details',

    ];
    protected $names = array(
            'name' => 0,
            'email' => 1,
            'password' => 2,
            'address' => 3,
            'dob' => 4,
            'phone_number' => 5,
            'gender' => 6,
            'role' => 7,
            'status' => 8,
            'details' => 9,

        );
    public static function writeDataToColumn9(string $filePath1,int $row,string $error)
    {
        // Lấy đường dẫn đến file Excel
        $filePath = storage_path('app/public/import_users/'. $filePath1);

        // Đọc dữ liệu từ file Excel vào đối tượng Spreadsheet
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Lấy số lượng dòng trong file Excel
        $rowCount = $worksheet->getHighestDataRow();
        $worksheet->getStyle('K1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF0000');

        // Ghi dữ liệu vào cột 9 của tất cả các dòng
            $worksheet->setCellValue('K' . $row, $error);

        // Lưu lại file Excel
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($filePath);
    }
    public static function validate($row){
        $validRoles = Role::pluck('id')->toArray();
        $rolesString = implode(',', $validRoles);

        $validator = Validator::make($row, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|max:255',
            'dob' => 'before:today|date_format:Y-m-d',
            'address' => 'required|max:255',
            'phone_number' => 'required|min:10|max:20',
            'gender' => 'required|in:1,2,3',
            'role' => 'required|in:'. $rolesString,
            'status' => 'required|in:0,1',
            'details' => 'required|string'
        ], [
            'name.required' => 'Vui lòng nhập tên',
            'name.string' => 'Tên vui lòng nhập chữ',
            'details.string' => 'Mô tả vui lòng nhập chữ',
            'name.max' => 'Tên không được vượt quá 255 ký tự',
            'password.max' => 'password không được vượt quá 255 ký tự',
            'password.min' => 'password không được ngắn hơn 6 ký tự',
            'dob.before' => 'Ngày sinh phải nhỏ hơn hôm nay',
            'dob.date_format' => 'Ngày sinh không đúng định dạng',
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không đúng định dạng',
            'email.unique' => 'Email đã được sử dụng',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'address.required' => 'Vui lòng nhập địa chỉ',
            'address.max' => 'Địa chỉ không được vượt quá 255 ký tự',
            'phone_number.required' => 'Vui lòng nhập số điện thoại',
            'phone_number.max' => 'Số điện thoại không được vượt quá 20 ký tự',
            'phone_number.min' => 'Số điện thoại không được ngắn hơn 10 ký tự',
            'gender.required' => 'Vui lòng chọn giới tính',
            'gender.in' => 'Giới tính không hợp lệ',
            'role.required' => 'Vui lòng chọn vai trò',
            'role.in' => 'Vai trò không hợp lệ',
            'status.required' => 'Vui lòng chọn trạng thái',
            'details.required' => 'Vui lòng chọn mô tả',
            'status.in' => 'Trạng thái không hợp lệ',
        ]);

        if ($validator->fails()) {
            return $validator->messages()->all();
        }
        return "1";

    }



    public function startRow(): int
    {
        return 2;
    }
    public function chunkSize(): int
    {
        return 100;
    }
}
