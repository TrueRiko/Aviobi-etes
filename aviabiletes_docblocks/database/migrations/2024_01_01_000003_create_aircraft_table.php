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
        Schema::create('aircraft', function (Blueprint $table) {
            $table->id();
            $table->string('model', 100);
            $table->integer('total_seats');
            $table->integer('rows');
            $table->string('seats_per_row', 10); // e.g., "ABC-DEF" for 3-aisle-3
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
        Schema::dropIfExists('aircraft');
    }
};
