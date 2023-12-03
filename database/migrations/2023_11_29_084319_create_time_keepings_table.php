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
        Schema::create('time_keepings', function (Blueprint $table) {
            $table->id();
            $table->string('month', 50);
            $table->unsignedInteger('user_id');
            $table->json('time');
            $table->integer('late');
            $table->integer('forget');
            $table->double('paid_leave');
            $table->double('unpaid_leave');
            $table->double('day_off');
            $table->double('punish');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_keepings');
    }
};
