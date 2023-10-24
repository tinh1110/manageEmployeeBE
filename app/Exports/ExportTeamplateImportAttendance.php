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
            'STT' => '1',
            'type_id' => '1',
            'start_date' => '2003-10-11',
            'end_date' => '2003-10-12',
            'start_time' => '10:12:12',
            'end_time' => '10:12:12',
            'reason' => 'dau bung',
            'status' => '1',
            'result' => 'dang duyet',
            'ids' => '2,3,4',
            'approver_id'=> '2',
            'approved_at'=> '2023-08-22 07:17:51',
            'img'=> ''
        ];
        return [$data];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'STT',
            'type_id',
            'start_date',
            'end_date',
            'start_time',
            'end_time',
            'reason',
            'status',
            'result',
            'ids ',
            'approver_id',
            'approved_at',
            'img',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        for ($row = 2; $row <= $lastRow; $row++) {
            $sheet->getStyle("A{$row}:Z{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }
        return [
            // Style the first row as bold text.
            1    => [
                'font' => ['bold' => true],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ],
        ];
    }

    /**
     * @return array
     */
    public function map($row): array
    {
        return [
            $row['STT'],
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
            $row['img']
        ];
    }

}
