<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_attendances', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('attendance_id');
            $table->tinyInteger('role_type')->comment('1:Người duyệt, 2:Người xem');

            $table->primary(['user_id','attendance_id']);
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('attendance_id')->references('id')->on('attendances');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_attendances');
    }
};
