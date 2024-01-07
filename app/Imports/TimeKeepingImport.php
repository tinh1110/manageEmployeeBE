<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\TimeKeeping;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TimeKeepingImport implements ToCollection, WithStartRow
{
    public function collection(Collection $rows)
    {
        $month = $rows[3][2];
        $year = $rows[4][2];
        $monthFull = $month.'-'.$year;
        $date = Carbon::createFromFormat('m-Y', $monthFull);
        $start = $date->format('Y-m-01');
        $end = $date->format('Y-m-t');

        $checkInTime = Carbon::parse('08:00:00');
        $checkOutTime = Carbon::parse('17:00:00');

        $data = []; // Mảng 3 chiều để lưu thông tin
        $employeeData = []; // Mảng để lưu thông tin của mỗi nhân viên

        // Tách dữ liệu thành từng dòng
        $rows = $rows->slice(8);

        foreach ($rows as $line) {

            // Lấy thông tin về mã nhân viên và tên nhân viên
            $user_id = $line[0] ?: null;
            if (is_null($user_id)) {
                break;
            }
            $user_name = $line[1];
            // Khởi tạo mảng để lưu thông tin ngày làm việc và giờ check-in, check-out của nhân viên
            $employeeSchedule = [];

            $total = 0;

            // Lặp qua các cột dữ liệu từ cột 3 trở đi
            $day = 1;
            $paidDay = 0;
            $late = 0;
            $forget = 0;

            for ($i = 3; $i < 64; $i += 2) {
                if (checkdate($month, $day, $year)) {
                    $date = Carbon::create($year, $month, $day);
                    // Kiểm tra xem ngày có phải là thứ 7 hoặc chủ nhật không
                    if ($date->isSaturday() || $date->isSunday()) {
                        $employeeSchedule[] = [
                            'day' => $day,
                            'Check-in' => null,
                            'Check-out' => null,
                        ];
                        $day++;
                    } else {
                        $checkIn = $line[$i] ? Date::excelToDateTimeObject($line[$i])->format('H:i:s') : null; // Giờ check-in
                        $checkOut = $line[$i + 1] ? Date::excelToDateTimeObject($line[$i + 1])->format('H:i:s') : null; // Giờ check-out
//                  dd( Carbon::parse(date('H:i:s', ceil(strtotime("17:00:00") / (15 * 60)) * (15 * 60)))->diffInMinutes($checkOutTime));
                        if (is_null($checkIn)) {
                            if ($checkOut) {
                                $forget++;
                                $checkIn = \DateTime::createFromFormat('H:i:s', '8:00:00')->format('H:i:s');
                            } else {
                                $attendance = Attendance::where('created_by_id', $user_id)->where('status',
                                    Attendance::STATUS_APPROVED)->whereIn('type_id', [6, 7])->where(function ($query
                                ) use ($date) {
                                    $query->where('start_date', '<=', $date)
                                        ->where('end_date', '>=', $date);
                                })->get();

                                if (!$attendance) {
                                    $forget += 2;
                                }
                                $checkIn = \DateTime::createFromFormat('H:i:s', '8:00:00')->format('H:i:s');
                                $checkOut = \DateTime::createFromFormat('H:i:s', '17:00:00')->format('H:i:s');
                            }
                        } else {
                            if (is_null($checkOut)) {
                                $forget++;
                                $checkOut = \DateTime::createFromFormat('H:i:s', '17:00:00')->format('H:i:s');
                            }
                        }
                        $checkInLate = Carbon::parse(date('H:i:s',
                            ceil(strtotime($checkIn) / (15 * 60)) * (15 * 60)))->diffInMinutes($checkInTime);
                        $checkOutEarly = Carbon::parse(date('H:i:s',
                            ceil(strtotime($checkOut) / (15 * 60)) * (15 * 60)))->diffInMinutes($checkOutTime);
                        if ($checkInLate > 0) {
                            $total += $checkInLate / 480; // chia theo ngày công, 1 ngày công 480p.
                            $late++;
                        }
                        if ($checkOutEarly < 0) {
                            $total += $checkOutEarly / 480;
                        }
                        // Lưu thông tin vào mảng 2 chiều

                        $employeeSchedule[] = [
                            'day' => $day,
                            'Check-in' => $checkIn,
                            'Check-out' => $checkOut,
                        ];
                        $day++;
                        $paidDay++;
                    }
                }
            }

            // Lưu thông tin của nhân viên vào mảng 3 chiều
            $employeeData[] = [
                'month' => $monthFull,
                'user_id' => $user_id,
                'user_name' => $user_name,
                'time' => $employeeSchedule,
                'total' => $total,
                'total_day' => $paidDay,
                'late' => $late,
                'forget' => $forget,
            ];

        }
        foreach ($employeeData as $record) {
            $user_id = $record['user_id'];
            $user = User::where('id', $user_id)->first();
            $dayoff = $user->day_off;
            $check = TimeKeeping::where('month', $monthFull)->where('user_id', $user_id)->orderByDesc('id')->first();
            if ($check){
                if ($dayoff > 0) $dayoff = $dayoff + $check->paid_leave;
                else $dayoff = $check->paid_leave - $check->total_day + $check->day_work + $check->unpaid_leave;
            }else
            {
                $dayoff += 1;
            }
            $attendancesAll = Attendance::where('created_by_id', $user_id)->where('status',
                Attendance::STATUS_APPROVED)->where(function ($query) use ($start, $end) {
                $query->where('start_date', '<=', $end)
                    ->where('end_date', '>=', $start);
            })->get();
            $attendanceLate = Attendance::where('created_by_id', $user_id)->where('status',
                Attendance::STATUS_APPROVED)->whereIn('type_id', [1, 2])->where(function ($query) use ($start, $end) {
                $query->where('start_date', '<=', $end)
                    ->where('end_date', '>=', $start);
            })->get();
            $record['paid_leave'] = $attendancesAll->sum('total_hours') / 8;//nghi co phep
            $sum = $attendanceLate->sum('total_hours') / 8;
            $record['unpaid_leave'] = abs($record['total'] - $sum);
            $parts = explode("-", $monthFull);
            $year = $parts[1];
            $record['day_work'] = $record['total_day'] - $record['unpaid_leave'] - $record['paid_leave'];
            if ($record['paid_leave'] > 0) {
                if ($record['paid_leave'] >= $dayoff) {
                    $dayoff = 0;
                    $record['day_work'] += $dayoff;
                } else {
                    $dayoff -= $record['paid_leave'];
                    $record['day_work'] += $record['paid_leave'];
                }
            }

            if ($check) {
                unset($record['total']);
                TimeKeeping::where('id', $check->id)->update($record);
            } else {
                TimeKeeping::create($record);
            }
            $list = TimeKeeping::where('user_id', $user_id)->where('month', 'LIKE', '%' . $year . '%')->get();
            $paidDay = abs($list->sum('paid_leave'));
            $unpaidDay = abs($list->sum('unpaid_leave'));

            User::where('id', $user_id)->update([
                'day_off' => abs($dayoff), 'unpaid_day' => $unpaidDay, 'paid_day' => $paidDay
            ]);
        }
    }

    public function startRow(): int
    {
        return 1;
    }

}
