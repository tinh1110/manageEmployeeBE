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
            'address',
            'phone_number',
            'dob',
            'gender',
            'role',
            'status',
            'details',
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

    /**
    * @return array
    */
    public function map($row): array
    {
        $gender = User::getGender($row->gender);
        $status = User::getStatus($row->status);
        return [
            $row->name,
            $row->email,
            $row->address,
            $row->phone_number,
            $row->dob,
            $gender,
            $row->role->role_name,
            $status,
            $row->details,
        ];
    }
}
