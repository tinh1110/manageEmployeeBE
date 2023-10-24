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
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email',255)->unique();
            $table->string('password',255);
            $table->string('avatar',255)->nullable();
            $table->string('address',255)->nullable();
            $table->string('phone_number',12)->nullable();
            $table->date('dob')->nullable();
            $table->text('details')->nullable();
            $table->tinyInteger('gender')->comment('1:Nam,2:Nữ');
            $table->tinyInteger('role_id')->notNull()->comment('1:Admin');
            $table->tinyInteger('status')->notNull()->comment('0:Active,1:Inactive');
            $table->unsignedInteger('created_by_id');
            $table->timestamp('created_at')->nullable();
            $table->unsignedInteger('updated_by_id')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('deleted_by_id')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
