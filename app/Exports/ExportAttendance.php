<?php

namespace App\Exports;

use App\Common\CommonConst;
use App\Http\Resources\Attendance\AttendanceResource;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportAttendance implements FromCollection, WithMapping, WithStrictNullComparison, WithHeadings, WithStyles
{
    private $collection;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return [
            'Created_by',
            'type',
            'start_date',
            'end_date',
            'start_time',
            'end_time',
            'reason',
            'status',
            'approver',
            'approved_at',
            'result',
            'managers',
        ];
    }

    public function map($attendance): array
    {
        $attendanceResource = AttendanceResource::make($attendance);
        if ($attendanceResource->status == CommonConst::NOT_REVIEWED) {
            $statusString = "NOT REVIEWED";
        }else if ($attendanceResource->status == CommonConst::ATTENDANCE_REJECT) {
            $statusString = " ATTENDANCE REJECT";
        }else $statusString = " ATTENDANCE ACCEPT";

        return [
            $attendanceResource->user->name,
            $attendanceResource->type->name,
            $attendanceResource->start_date,
            $attendanceResource->end_date,
            $attendanceResource->start_time,
            $attendanceResource->end_time,
            $attendanceResource->reason,
            $statusString,
            $attendanceResource->approver,
            $attendanceResource->approved_at,
            $attendanceResource->result,
            $attendanceResource->managers,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:L1')->applyFromArray([
            'fill' => [
                'fillType' => 'solid',
                'startColor' => [
                    'rgb' => 'FFA500', // Màu cam
                ],
            ],
        ]);
    }

}
