<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
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
    Route::put('/changePassword', [ProfileController::class, 'changePassword']);
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/user/show/{id}', [UserController::class, 'show']);
    Route::get('/user/profile/{id}', [ProfileController::class, 'user']);
    Route::get('/admin', [AdminController::class, 'getAdmin']);
    Route::get('/get-all', [UserController::class, 'getAll']);
    Route::get('/foo', [UserController::class, 'foo']);


    Route::prefix('/user')->name('user.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('list');
        Route::post('/store', [UserController::class, 'store'])->name('store');
        Route::put('/update/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [UserController::class, 'delete'])->name('delete');
        Route::delete('/forceDelete/{id}', [UserController::class, 'forceDelete'])->name('force_delete');
        Route::get('/deleted', [UserController::class, 'getUsersDeleted'])->name('list_deleted');
//        Route::get('/exportTemplate', [UserController::class, 'exportTemplate'])->name('export_template');
        Route::post('/restore/{id}', [UserController::class, 'restore'])->name('restore');
//        Route::delete('/forceDeleteMulti', [UserController::class, 'forceDeleteMulti']);
//        Route::post('/restoreMulti', [UserController::class, 'restoreMulti']);
        Route::post('/import', [AdminController::class, 'import'])->name('import_user');
    });

    Route::post('/import/time', [AdminController::class, 'importTime'])->name('time');
    Route::get('/time', [AdminController::class, 'timeUser']);
    Route::get('/timeList', [AdminController::class, 'timeList']);
    Route::get('/time/export', [AdminController::class, 'exportTime']);
    Route::get('/user/file-import', [AdminController::class, 'importView']);
    Route::post('/user/importUser', [UserController::class, 'importUser']);
    Route::get('/user/exportUser', [UserController::class, 'exportUser']);
    Route::get('/user/export-template', [AdminController::class, 'exportTemplate']);

    Route::get('/event/edit/{id}', [EventController::class, 'edit']);
    Route::get('/event/type', [EventController::class, 'typeEvent']);

    Route::prefix('/event')->name('event.')->group(function () {
        Route::get('/', [EventController::class, 'index'])->name('list');
        Route::post('/store', [EventController::class, 'store'])->name('store');
        Route::put('/update/{id}', [EventController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [EventController::class, 'delete'])->name('delete');
    });

    Route::get('/role/get-detail-role/{id}', [RoleController::class, 'getDetailRole']);
    Route::prefix('/role')->name('role.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->name('list');
        Route::post('/create-role', [RoleController::class, 'create'])->name('create');
        Route::put('/update-role/{id}', [RoleController::class, 'update'])->name('update');
        Route::delete('/delete-role/{id}', [RoleController::class, 'delete'])->name('delete');
        Route::get('/listRoute', [RoleController::class, 'listRoute'])->name('get_list');
        Route::put('/changePermission/{id}', [RoleController::class, 'changePermission'])->name('change_permission');
    });

    Route::get('/team/get-detail-team/{id}', [TeamController::class, 'getDetailTeam']);

    Route::prefix('/team')->name('project.')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('list');
        Route::post('/create-new-team', [TeamController::class, 'createNewTeam'])->name('create');
        Route::put('/update-team/{id}', [TeamController::class, 'updateTeam'])->name('update');
        Route::delete('/delete-team/{id}', [TeamController::class, 'deleteTeam'])->name('delete');
//        Route::get('/get-list-sub/{id}', [TeamController::class, 'getListSubTeam'])->name('listSub');
        Route::get('/all-list-main-team', [TeamController::class, 'getAllMainTeam'])->name('getList');
//        Route::get('/all-list-sub-team', [TeamController::class, 'allListSubTeam'])->name('getAllListSub');
        Route::get('/get-list-user-of-team/{id}', [TeamController::class, 'getListUserOfTeam'])->name('getListUser');
        Route::post('/add-member/{id}', [TeamController::class, 'addMember'])->name('addMember');
        Route::delete('/remove-member/{id}', [TeamController::class, 'delete'])->name('removeMember');

    });
    Route::get('/attendance/show/{id}', [AttendanceController::class, 'show']);
    Route::get('/attendance/type', [AttendanceController::class, 'type']);
    Route::get('/attendance/exportAll', [AttendanceController::class, 'exportAll']);


    Route::prefix('/attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('list');
        Route::post('/store', [AttendanceController::class, 'store'])->name('store');
        Route::put('/update/{id}', [AttendanceController::class, 'update'])->name('update');
        Route::delete('/delete/{id}', [AttendanceController::class, 'delete'])->name('delete');
        Route::put('/accept/{id}', [AttendanceController::class, 'review'])->name('accept');
        Route::put('/reject/{id}', [AttendanceController::class, 'review'])->name('reject');
        Route::get('/export', [AttendanceController::class, 'export'])->name('export');
//        Route::post('/importAttendance', [AttendanceController::class, 'importAttendance'])->name('import');
//        Route::get('/get-importAttendance', [AttendanceController::class, 'statisticalFileImport'])->name('importedList');
//        Route::get('/export-templateImportAttendance', [AttendanceController::class, 'exportTemplate'])->name('exportTemplate');
    });

    Route::get('/attendance/all', [AttendanceController::class, 'all']);
    Route::prefix('/comment')->group(function () {
        Route::get('/', [CommentController::class, 'index']);
        Route::get('/{id}', [CommentController::class, 'getEventComment']);
        Route::get('/{id}/{parent_id}', [CommentController::class, 'getChildrenComment']);
        Route::post('/store', [CommentController::class, 'store']);
        Route::get('/edit/{id}', [CommentController::class, 'edit']);
        Route::put('/update/{id}', [CommentController::class, 'update']);
        Route::delete('/delete/{id}', [CommentController::class, 'delete']);
    });
});

