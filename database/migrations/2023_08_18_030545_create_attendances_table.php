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
        Schema::create('attendances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('created_by_id');
            $table->tinyInteger('type_id');
            $table->unsignedDouble('total_hours');
            $table->date('start_date')->notnull();
            $table->date('end_date')->notnull();
            $table->time('start_time')->notnull();
            $table->time('end_time')->notnull();
            $table->text('reason')->nullable();
            $table->string('img',255)->nullable();
            $table->tinyInteger('status')->default('0')->comment('0:chưa duyệt, 1:đồng ý, 2:từ chối');
            $table->timestamp('created_at')->nullable();
            $table->integer('approver_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('result')->nullable();
            $table->unsignedInteger('updated_by_id')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('deleted_by_id')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('type_id')->references('id')->on('attendance_types');
            $table->foreign('created_by_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
