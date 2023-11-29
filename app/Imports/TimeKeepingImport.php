<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TimeKeepingImport implements ToCollection,WithStartRow
{

    public function collection(Collection $rows)
    {
        $data = []; // Mảng 3 chiều để lưu thông tin

        $employeeData = []; // Mảng để lưu thông tin của mỗi nhân viên
        $daysOfWeek = ["Thứ hai", "Thứ ba", "Thứ tư", "Thứ năm", "Thứ sáu", "Thứ bảy", "Chủ nhật"]; // Danh sách các ngày trong tuần

        // Tách dữ liệu thành từng dòng

        foreach ($rows as $line) {
            $fields = explode("\t", $line); // Tách dữ liệu trong từng dòng thành từng trường

            // Lấy thông tin về mã nhân viên và tên nhân viên
            $employeeCode = $line[0];
            $employeeName = $line[1];

            // Khởi tạo mảng để lưu thông tin ngày làm việc và giờ check-in, check-out của nhân viên
            $employeeSchedule = [];

            // Lặp qua các cột dữ liệu từ cột 3 trở đi

            for ($i = 2; $i < 65; $i += 2) {
                $dayIndex = ($i - 2) / 2; // Chỉ số của ngày trong tuần
                $dayOfWeek = $daysOfWeek[$dayIndex]; // Ngày trong tuần

                $checkIn = $line[$i]; // Giờ check-in
                $checkOut = $line[$i + 1]; // Giờ check-out

                // Lưu thông tin vào mảng 2 chiều
                $employeeSchedule[] = [
                    'Ngày làm việc' => $dayOfWeek,
                    'Check-in' => $checkIn,
                    'Check-out' => $checkOut
                ];
            }

            // Lưu thông tin của nhân viên vào mảng 3 chiều
            $employeeData[] = [
                'Mã nhân viên' => $employeeCode,
                'Tên nhân viên' => $employeeName,
                'Lịch làm việc' => $employeeSchedule
            ];
        }

        $data[] = $employeeData;

        dd($data);
    }

    public function startRow(): int
    {
        return 9;
    }

}
