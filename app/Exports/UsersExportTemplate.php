<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExportTemplate implements WithHeadings, WithMapping, FromArray, ShouldAutoSize, WithStyles
{
    /**
    * @return array
    */
    public function array(): array
    {
        $data = [
            'name' => 'Tran Van A',
            'email' => 'a@gmail.com',
            'password' => '123456',
            'address' => 'Ha Noi',
            'phone_number' => '0123456789',
            'dob' => '01/01/2023',
            'details' => 'abcde',
            'gender' => '1',
            'role_id' => '1',
            'status' => '1'
        ];
        return [$data];
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
