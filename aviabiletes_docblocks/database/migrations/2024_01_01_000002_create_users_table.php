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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('password');
            $table->foreignId('role_id')->constrained();
            $table->string('preferred_language', 2)->default('en');
            $table->string('homepage_sort', 20)->default('departure_time');
            $table->json('preferred_aircraft_types')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
