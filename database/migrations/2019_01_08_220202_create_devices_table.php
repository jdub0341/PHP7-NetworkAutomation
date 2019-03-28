<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->string('name')->nullable();
            $table->json('data')->nullable();
            $table->string('vendor')->nullable();
            $table->string('model')->nullable();
            $table->string('serial')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['type']);
            $table->index(['ip']);
            $table->index(['name']);
            $table->index(['vendor']);
            $table->index(['model']);
            $table->index(['serial']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('devices');
    }
}
