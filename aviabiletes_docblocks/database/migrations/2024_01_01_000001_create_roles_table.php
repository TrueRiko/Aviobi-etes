<?php

/**
 * Laravel Airline Reservation System
 *
 * This file is part of the application logic for booking and managing airline flights.
 */


/**
 * Import required Laravel or application classes.
 */
use Illuminate\Database\Migrations\Migration;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Database\Schema\Blueprint;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
 * Up
 *
 * @param void
 * @return mixed
 */
public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->timestamps();
        });
    }

    /**
 * Down
 *
 * @param void
 * @return mixed
 */
public function down()
    {
        Schema::dropIfExists('roles');
    }
};
