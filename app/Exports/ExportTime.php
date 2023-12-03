<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportTime implements FromCollection, WithMapping, WithStrictNullComparison, WithHeadings, WithStyles
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
            'Id người dùng',
            'Tên',
            'Dữ liệu',
            'Lần đi muộn',
            'Quên chấm công',
            'Ngày nghỉ có phép',
            'Ngày nghỉ không phép',
            'Ngày làm việc',
            'Ngày nghỉ còn lại',
        ];
    }

    public function map($row): array
    {
        $day_off = $row->user->day_off;
        return [
            $row->user_id,
            $row->user_name,
            $row->time,
            $row->late,
            $row->forget,
            $row->paid_leave,
            $row->unpaid_leave,
            $row->day_work,
            $day_off,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:I1')->applyFromArray([
            'fill' => [
                'fillType' => 'solid',
                'startColor' => [
                    'rgb' => 'FFA500', // Màu cam
                ],
            ],
        ]);
    }
}
