<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RegistrationVerified extends Mailable
{
    use Queueable, SerializesModels;

    public $registration;
    public $type;
    public $itemName;
    public $qrCodeUrl;

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
            try {
                $response = Http::get($this->qrCodeUrl);
                
                if ($response->successful()) {
                    $qrCodeData = $response->body();
                }
            } catch (\Exception $e) {
                Log::error("Gagal download QR Code dari QuickChart: " . $e->getMessage());
            }
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