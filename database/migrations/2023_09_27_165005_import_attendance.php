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
        Schema::create('imported_attendances', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('created_by_id')->notnull();
            $table->string('file_name')->notnull();
            $table->tinyInteger('status')->notnull()->comment('0:đang import,1:import xong');
            $table->unsignedInteger('success_amount')->default(0);
            $table->unsignedInteger('fail_amount')->default(0);
            $table->string('error',255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->foreign('created_by_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('imported_attendances');
    }
};
