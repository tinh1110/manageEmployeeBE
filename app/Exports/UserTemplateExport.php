<?php


namespace App\Exports;

use App\Models\Role;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UserTemplateExport implements  FromArray, WithMapping, WithStrictNullComparison,WithHeadings,WithStyles
{

    public function array(): array
    {
        return [
            [
                'name' => 'Nguyen Van A',
                'email' => 'a@gmail.com',
                'password' => '123456',
                'address' => 'Hoa Lu, Ninh Binh',
                'dob' => "2001-11-10",
                'phone_number' => "0912982433",
                'gender' => '1',
                'role' => '1',
                'status' => '1',
                'details' => 'abc',
            ]
        ];
    }

    public function headings(): array
    {
        $roles = Role::where('deleted_at',null)->get();
        $rolesString = 'Vai trò(';
        foreach ($roles as $role) {
            $rolesString .= $role->id . ':' . $role->role_name . ', ';
        }
        $rolesString = rtrim($rolesString, ', ');

        return [
            'Tên Nhân Viên',
            'Email',
            'Password',
            'Địa Chỉ',
            'Ngày sinh(yyyy-mm-dd)',
            'Số điện thoại',
            'Giới tính(1:Nam, 2:Nữ, 3:Khác)',
            $rolesString.')',
            'Trạng thái(0:Active, 1:Inactive)',
            'Chi tiết'
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'fill' => [
                'fillType' => 'solid',
                'startColor' => [
                    'rgb' => 'FFA500', // Màu cam
                ],
            ],
        ]);
    }

    public function map($row): array
    {
      return [
          $row['name'],
          $row['email'],
          $row['password'],
          $row['address'],
          $row['dob'],
          $row['phone_number'],
          $row['gender'],
          $row['role'],
          $row['status'],
          $row['details'],
      ];
    }

    public function query()
    {
        // TODO: Implement query() method.
    }
}
