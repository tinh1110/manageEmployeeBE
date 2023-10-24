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
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->tinyInteger('type_id')->comment('1: ngày nghỉ lễ, 2: Sự kiện quan trọng, 3: teambuilding, 4:seminar, 5:Khác');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->text('description')->nullable();
            $table->text('location')->nullable();
            $table->json('image')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->unsignedInteger('created_by_id');
            $table->timestamp('created_at')->nullable();
            $table->unsignedInteger('updated_by_id')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('deleted_by_id')->nullable();
            $table->timestamp('deleted_at')->nullable();

            $table->foreign('type_id')->references('id')->on('event_types');
            $table->foreign('created_by_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
