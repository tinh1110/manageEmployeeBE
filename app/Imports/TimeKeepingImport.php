<?php

namespace App\Imports;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TimeKeepingImport implements ToCollection,WithStartRow
{
    public function collection(Collection $rows)
    {

            $month = $rows[3][2] . '/' . $rows[4][2];
            $date = Carbon::createFromFormat('m/Y', $month);
            $start = $date->format('01-m-Y');
            $end = $date->format('t-m-Y');

            $data = []; // Mảng 3 chiều để lưu thông tin
        $employeeData = []; // Mảng để lưu thông tin của mỗi nhân viên

        // Tách dữ liệu thành từng dòng
        $rows = $rows->slice(8);

        foreach ($rows as $line) {

            // Lấy thông tin về mã nhân viên và tên nhân viên
            $user_id = $line[0] ?: null;
            if (is_null($user_id)) break;
            $user_name = $line[1];
            // Khởi tạo mảng để lưu thông tin ngày làm việc và giờ check-in, check-out của nhân viên
            $employeeSchedule = [];

            // Lặp qua các cột dữ liệu từ cột 3 trở đi
            $day = 1;

            for ($i = 3; $i < 64; $i += 2) {
                $date = $day . '/' . $month;
                $checkIn = $line[$i] ? Date::excelToDateTimeObject($line[$i])->format('H:i:s') : null ; // Giờ check-in
                $checkOut = $line[$i + 1] ? Date::excelToDateTimeObject($line[$i + 1])->format('H:i:s') : null; // Giờ check-out
//                dd(date('H:i:s', ceil(strtotime($checkIn) / (15 * 60)) * (15 * 60)));
                // Lưu thông tin vào mảng 2 chiều
                $employeeSchedule[] = [
                    'Ngày làm việc' => $day,
                    'Check-in' => $checkIn,
                    'Check-out' => $checkOut
                ];
                $day++;
            }

            // Lưu thông tin của nhân viên vào mảng 3 chiều
            $employeeData[] = [
                'user_id' => $user_id,
                'user_name' => $user_name,
                'timeKeeping' => $employeeSchedule
            ];

        }
        $data[] = $employeeData;

        foreach ($data as $record){
            $user_id = $record->user_id;
            $attendances = Attendance::where('created_by_id',$user_id)->where('status',Attendance::STATUS_APPROVED)->where(function ($query) use ($start, $end) {
                $query->where('start_date', '<=', $end)
                    ->where('end_date', '>=', $start);
            })->get();
            foreach ($attendances as $attendance){
                $attendance
            }
        }
        dd($data);
    }

    public function startRow(): int
    {
        return 1;
    }

}
