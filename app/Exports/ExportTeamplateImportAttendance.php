<?php

namespace App\Exports;

use App\Common\CommonConst;
use App\Http\Resources\Attendance\AttendanceResource;
use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportTeamplateImportAttendance implements WithHeadings, WithMapping, FromArray, ShouldAutoSize, WithStyles
{

    /**
     * @return array
     */
    public function array(): array
    {
        $data = [
            'user_id' => '1',
            'type_id' => '1',
            'start_date' => '2003-10-11',
            'end_date' => '2003-10-12',
            'start_time' => '10:12:12',
            'end_time' => '10:12:12',
            'reason' => 'dau bung',
            'status' => '1',
            'result' => 'ok ok',
            'ids' => '1,9',
            'approver_id'=> '1',
            'approved_at'=> '2023-08-22 07:17:51',
        ];
        return [$data];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'user_id',
            'type_id(Loại nghỉ)',
            'start_date',
            'end_date',
            'start_time',
            'end_time',
            'reason',
            'status(0:Chưa duyệt, 1: đã duyệt, 2: từ chối)',
            'result',
            'ids',
            'approver_id',
            'approved_at',
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

    /**
     * @return array
     */
    public function map($row): array
    {
        return [
            $row['user_id'],
            $row['type_id'],
            $row['start_date'],
            $row['end_date'],
            $row['start_time'],
            $row['end_time'],
            $row['reason'],
            $row['status'],
            $row['result'],
            $row['ids'],
            $row['approver_id'],
            $row['approved_at'],
        ];
    }

}
