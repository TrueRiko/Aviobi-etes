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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('flight_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('seat', 5);
            $table->decimal('price', 10, 2);
            $table->enum('status', ['reserved', 'paid', 'cancelled'])->default('reserved');
            $table->enum('seat_type', ['window', 'middle', 'aisle']);
            $table->timestamp('reserved_until')->nullable();
            $table->timestamps();
            
            $table->unique(['flight_id', 'seat']);
            $table->index(['user_id', 'status']);
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
        Schema::dropIfExists('bookings');
    }
};
