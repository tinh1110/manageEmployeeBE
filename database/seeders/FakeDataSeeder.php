<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class FakeDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

    $event_types = ['Ngày nghỉ lễ','Sự kiện quan trọng','TeamBuilding','Seminar','Khác'];
        foreach ($event_types as $key => $value){
            DB::table('event_types')->insert([
            'id' => $key+1,
            'name' => $value,
            'created_by_id' => 1,
        ]);
    }

        $routeCollection = Route::getRoutes();
        $routeNames = [];
        foreach ($routeCollection as $route) {
            if (str_contains($route->getAction()['prefix'], 'api')) {
                $routeNames[] = $route->getName();
            }
        }
        foreach ($routeNames as $value) {
            if ($value !== null) {
                $result[] = $value;
            }
        }
        $data['role_permissions'] = $result;

        DB::table('roles')->insert([
            'id' => 1,
            'role_name' => 'Admin',
            'role_permissions' => json_encode($data['role_permissions']),
            'description' => 'Admin quản lý thêm, sửa, xóa, import, export nhân viên; thêm, sửa, xóa, Role; Duyệt Attendance.',
            'created_by_id' => 1,
            'created_at' => now(),
        ]);

        DB::table('users')->insert([
            'id' => 1,
            'name' => 'test1',
            'email' => 'test1@gmail.com',
            'address' => "ha noi",
            'phone_number' => "0912928192",
            'gender' => 1,
            'role_id' => 1,
            'status' => 0,
            'created_by_id' => 1,
            'created_at' => now(),
            'password' => Hash::make('123456'),
        ]);
        for ($i = 0; $i < 5; $i++) {
            DB::table('users')->insert([
                'name' => Str::random(10),
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('123456'),
                'address' => Str::random(10),
                'phone_number' => '0123456789',
                'gender' => 1,
                'status' => 0,
                'role_id' => 1,
                'created_at' => now(),
                'created_by_id' => 1,
            ]);

        }

        $attendanceTypes = [
            ['id'=> 1,'name'=>'Đi muộn', 'created_by_id' => 1],
            ['id'=> 2,'name'=>'Về sớm', 'created_by_id' => 1],
            ['id'=> 3,'name'=>'Nghỉ giữa giờ', 'created_by_id' => 1],
            ['id'=> 4,'name'=>'Nghỉ buổi sáng', 'created_by_id' => 1],
            ['id'=> 5,'name'=>'Nghỉ buổi chiều', 'created_by_id' => 1],
            ['id'=> 6,'name'=>'Nghỉ cả ngày', 'created_by_id' => 1],
            ['id'=> 7,'name'=>'Remote buổi sáng', 'created_by_id' => 1],
            ['id'=> 8,'name'=>'Remote buổi chiều', 'created_by_id' => 1],
            ['id'=> 9,'name'=>'Remote cả ngày', 'created_by_id' => 1],
            ['id'=> 10,'name'=>'Khác', 'created_by_id' => 1],
        ];

        DB::table('attendance_types')->insert($attendanceTypes);

    }
}
