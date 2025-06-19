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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('bank_name', 100);
            $table->string('account_number', 50);
            $table->boolean('is_primary')->default(false);
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
        Schema::dropIfExists('payments');
    }
};
