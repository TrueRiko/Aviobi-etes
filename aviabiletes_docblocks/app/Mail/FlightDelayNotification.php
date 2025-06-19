<?php

/**
 * Laravel Airline Reservation System
 *
 * This file is part of the application logic for booking and managing airline flights.
 */


/**
 * Define which namespace this file belongs to.
 */
namespace App\Mail;

/**
 * Import required Laravel or application classes.
 */
use Illuminate\Bus\Queueable;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Mail\Mailable;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Mail\Mailables\Content;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Mail\Mailables\Envelope;
/**
 * Import required Laravel or application classes.
 */
use Illuminate\Queue\SerializesModels;

/**
 * Class FlightDelayNotification
 *
 * Describe the purpose and responsibilities of this class.
 */
class FlightDelayNotification extends Mailable
{
    /**
 * Import required Laravel or application classes.
 */
use Queueable, SerializesModels;

    public $notification;
    public $flight;

    /**
 *   construct
 *
 * @param $notification, $flight
 * @return mixed
 */
public function __construct($notification, $flight)
    {
        $this->notification = $notification;
        $this->flight = $flight;
    }

    /**
 * Envelope
 *
 * @param void
 * @return mixed
 */
public function envelope()
    {
        return new Envelope(
            subject: __('messages.flight_delay_subject', [
                'flight' => $this->flight->flight_number
            ]),
        );
    }

    /**
 * Content
 *
 * @param void
 * @return mixed
 */
public function content()
    {
        return new Content(
            view: 'emails.flight-delay',
        );
    }
}
