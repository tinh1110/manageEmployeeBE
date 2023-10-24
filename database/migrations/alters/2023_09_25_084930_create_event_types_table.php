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
        Schema::create('event_types', function (Blueprint $table) {
            $table->tinyInteger('id')->autoIncrement();
            $table->string('name',255);
            $table->unsignedInteger('created_by_id');
            $table->timestamp('created_at')->nullable();
            $table->unsignedInteger('updated_by_id')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unsignedInteger('deleted_by_id')->nullable();
            $table->timestamp('deleted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_types');
    }
};
