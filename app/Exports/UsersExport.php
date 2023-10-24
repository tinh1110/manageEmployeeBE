<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    protected $users;

    public function __construct($users)
    {
        $this->users = $users;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->users;
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        return [
            'name',
            'email',
            'password',
            'address',
            'phone_number',
            'dob',
            'details',
            'gender',
            'role_id',
            'status'
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
            $row['name'],
            $row['email'],
            $row['password'],
            $row['address'],
            $row['phone_number'],
            $row['dob'],
            $row['details'],
            $row['gender'],
            $row['role_id'],
            $row['status'],
        ];
    }
}
