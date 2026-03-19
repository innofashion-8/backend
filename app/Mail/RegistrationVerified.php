<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RegistrationVerified extends Mailable
{
    use Queueable, SerializesModels;

    public $registration;
    public $type;
    public $itemName;
    public $qrCodeUrl; // 🔥 KITA CUMA SIMPAN STRING URL DI SINI

    public function __construct($registration)
    {
        $this->registration = $registration;
        
        if ($registration instanceof \App\Models\EventRegistration) {
            $this->type = 'EVENT';
            $this->itemName = $registration->event->title;
            $this->qrCodeUrl = "https://quickchart.io/qr?text=" . urlencode($registration->id) . "&size=300&margin=2";
            
        } else {
            $this->type = 'COMPETITION';
            $this->itemName = $registration->competition->name;
            $this->qrCodeUrl = null;
        }
    }

    public function build()
    {
        $qrCodeData = null;

        if ($this->type === 'EVENT' && $this->qrCodeUrl) {
            $qrCodeData = file_get_contents($this->qrCodeUrl);
        }

        $email = $this->subject("[ INNOFASHION 8 ] - PROTOCOL VERIFIED")
                      ->view('mails.registration.verified', [
                          'qrCodeData' => $qrCodeData 
                      ]);

        if ($this->type === 'EVENT' && $qrCodeData) {
            $email->attachData($qrCodeData, 'Access_Pass_Innofashion.png', [
                'mime' => 'image/png',
            ]);
        }

        return $email;
    }
}