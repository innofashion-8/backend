<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationRejected extends Mailable
{
    use Queueable, SerializesModels;

    public $registration;
    public $type;
    public $itemName;
    public $reason;

    public function __construct($registration, $reason)
    {
        $this->registration = $registration;
        $this->reason = $reason;

        if ($registration instanceof \App\Models\EventRegistration) {
            $this->type = 'EVENT';
            $this->itemName = $registration->event->title;
        } else {
            $this->type = 'COMPETITION';
            $this->itemName = $registration->competition->name;
        }
    }

    public function build()
    {
        return $this->subject("[ INNOFASHION 8 ] - REGISTRATION REJECTED")
                    ->view('mails.registration.rejected');
    }
}