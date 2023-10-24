<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\AttendanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware(['auth:sanctum', 'acl'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::put('/updateProfile', [ProfileController::class, 'updateProfile'])->name('update_profile');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('/user')->name('user.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('list');
        Route::post('/store', [UserController::class, 'store'])->name('store');
        Route::delete('/delete/{id}', [UserController::class, 'delete'])->name('delete');
        Route::delete('/forceDelete/{id}', [UserController::class, 'forceDelete'])->name('force_delete');
        Route::get('/show/{id}', [UserController::class, 'show'])->name('show');
        Route::put('/update/{id}', [UserController::class, 'update'])->name('update');
        Route::get('/exportTemplate', [UserController::class, 'exportTemplate'])->name('export_template');
        Route::post('/importUser', [UserController::class, 'importUser'])->name('import');
        Route::get('/exportUser', [UserController::class, 'exportUser'])->name('export');
        Route::delete('/deleteMulti', [UserController::class, 'deleteMulti'])->name('delete_multi');
        Route::delete('/forceDeleteMulti', [UserController::class, 'forceDeleteMulti'])->name('force_delete_multi');
        Route::post('/restore/{id}', [UserController::class, 'restore'])->name('restore');
        Route::post('/restoreMulti', [UserController::class, 'restoreMulti'])->name('restore_multi');
        Route::get('/get-all', [UserController::class, 'getAll'])->name('getall');
        Route::get('/file-import',[AdminController::class, 'importView'])->name('import_view');
        Route::post('/import', [AdminController::class, 'import'])->name('import_user');
        Route::get('/admin',[AdminController::class, 'getAdmin'])->name('getListAdmin');
        Route::get('/export-template',[AdminController::class, 'exportTemplate'])->name('export_template');

    });
    Route::prefix('/event')->name('event.')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('list');
        Route::get('/type', [EventController::class, 'typeEvent'])->name('getType');
        Route::post('/store', [EventController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [EventController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [EventController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [EventController::class, 'delete'])->name('delete');
    });

    Route::prefix('/role')->name('role.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('list');
        Route::post('/create-role', [RoleController::class, 'create'])->name('create');
        Route::get('/get-detail-role/{id}', [RoleController::class, 'getDetailRole'])->name('detail');
        Route::put('/update-role/{id}', [RoleController::class, 'update'])->name('update');
        Route::delete('/delete-role/{id}', [RoleController::class, 'delete'])->name('delete');
        Route::get('/listRoute', [RoleController::class, 'listRoute'])->name('get_list');
        Route::put('/changePermission/{id}', [RoleController::class, 'changePermission'])->name('change_permission');
    });

    Route::prefix('/team')->name('team.')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('list');
        Route::post('/create-new-team', [TeamController::class, 'createNewTeam'])->name('create');
        Route::get('/get-list-sub/{id}', [TeamController::class, 'getListSubTeam'])->name('listSub');
        Route::put('/update-team/{id}', [TeamController::class, 'updateTeam'])->name('update');
        Route::get('/get-detail-team/{id}', [TeamController::class, 'getDetailTeam'])->name('getDetail');
        Route::get('/get-list-user-of-team/{id}', [TeamController::class, 'getListUserOfTeam'])->name('getListUser');
        Route::post('/add-member/{id}', [TeamController::class, 'addMember'])->name('addMember');
        Route::delete('/remove-member/{id}', [TeamController::class, 'delete'])->name('removeMember');
        Route::delete('/delete-team/{id}', [TeamController::class, 'deleteTeam'])->name('delete');
        Route::get('/all-list-sub-team', [TeamController::class, 'allListSubTeam'])->name('getAllListSub');
        Route::get('/all-list-main-team', [TeamController::class, 'getAllMainTeam'])->name('getAllMainTeam');

    });
    Route::prefix('/attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('list');
        Route::post('/store', [AttendanceController::class, 'store'])->name('store');
        Route::delete('/delete/{id}', [AttendanceController::class, 'delete'])->name('delete');
        Route::get('/show/{id}', [AttendanceController::class, 'show'])->name('show');
        Route::put('/update/{id}', [AttendanceController::class, 'update'])->name('update');
        Route::put('/accept/{id}', [AttendanceController::class, 'review'])->name('accept');
        Route::put('/reject/{id}', [AttendanceController::class, 'review'])->name('reject');
        Route::get('/export', [AttendanceController::class, 'export'])->name('export');
        Route::post('/importAttendance', [AttendanceController::class, 'importAttendance'])->name('importAttendance');
        Route::get('/get-importAttendance', [AttendanceController::class, 'statisticalFileImport'])->name('statisticalFileImport');
        Route::get('/export-templateImportAttendance', [AttendanceController::class, 'exportTemplate'])->name('exportTemplate');
    });
});


