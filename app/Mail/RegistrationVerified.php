<?php

namespace App\Mail;

use App\Services\Attendance\AttendanceManagerFactory;
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
        $email = $this->subject("[ INNOFASHION 8 ] - PROTOCOL VERIFIED")
                      ->view('mails.registration.verified', [
                          // We pass the single QR data for backward compatibility in the view if needed
                          'qrCodeData' => null 
                      ]);

        if ($this->type === 'EVENT') {
            $manager = AttendanceManagerFactory::makeForEvent($this->registration->event);
            $tickets = $manager->getTickets($this->registration);

            $count = 1;
            foreach ($tickets as $ticket) {
                $url = "https://quickchart.io/qr?text=" . urlencode($ticket->ticketCode) . "&size=300&margin=2";
                try {
                    $response = Http::get($url);
                    
                    if ($response->successful()) {
                        $qrCodeData = $response->body();
                        $filename = $count++ . "_Access_Pass_" . str_replace(' ', '_', $ticket->guestName) . ".png";
                        
                        $email->attachData($qrCodeData, $filename, [
                            'mime' => 'image/png',
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error("Gagal download QR Code untuk tiket {$ticket->ticketCode}: " . $e->getMessage());
                }
            }
        }

        return $email;
    }
}