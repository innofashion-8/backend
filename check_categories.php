<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EventTicket;
use App\Models\CompetitionRegistration;
use App\Models\CompetitionMember;

// 1. SET PARTICIPANT USERS
$participantUserIds = collect();
$participantUserIds = $participantUserIds->merge(CompetitionRegistration::pluck('user_id'));
$participantUserIds = $participantUserIds->merge(CompetitionMember::pluck('user_id'));
$participantUserIds = $participantUserIds->unique()->toArray();

// 2. SET KELUARGA REGISTRATIONS (Ciri khas: Punya minimal 1 tiket bernama "Keluarga ...")
$keluargaRegIds = EventTicket::where('guest_name', 'LIKE', 'Keluarga %')
    ->pluck('event_registration_id')
    ->unique()
    ->toArray();

$tickets = EventTicket::with(['registration.user'])->get();

$categories = [
    'GUEST' => 0,
    'DFT22' => 0,
    'Participant' => 0,
    'DFT22_AND_Participant' => 0,
];

$overlapEmails = [];

foreach ($tickets as $ticket) {
    $reg = $ticket->registration;
    $user = $reg ? $reg->user : null;
    
    if (!$user) {
        $categories['GUEST']++;
        continue;
    }

    $isParticipant = in_array($user->id, $participantUserIds);
    $isKeluarga = in_array($reg->id, $keluargaRegIds);

    if ($isKeluarga && $isParticipant) {
        // Logika HashSet Priority:
        // Jika tiket ini adalah tiket utama si dobel jabatan
        if ($ticket->guest_name === $user->name) {
            $categories['DFT22_AND_Participant']++;
            $overlapEmails[] = $user->email;
        } else {
            // Tiket tamunya si dobel jabatan
            $categories['DFT22']++;
        }
    } elseif ($isKeluarga) {
        $categories['DFT22']++;
    } elseif ($isParticipant) {
        $categories['Participant']++;
    } else {
        $categories['GUEST']++;
    }
}

echo "=========================================\n";
echo "       HASIL ANALISA TIKET EVENT         \n";
echo "=========================================\n";
echo "Total Tiket di Database: " . $tickets->count() . "\n\n";

echo "Detail Kategori:\n";
echo "- GUEST                   : " . $categories['GUEST'] . " tiket\n";
echo "- DFT22 (Keluarga)        : " . $categories['DFT22'] . " tiket\n";
echo "- PARTICIPANT (Lomba)     : " . $categories['Participant'] . " tiket\n";
echo "- DFT22 + PARTICIPANT     : " . $categories['DFT22_AND_Participant'] . " tiket\n";

if (count($overlapEmails) > 0) {
    echo "\nOrang yang double jabatan (DFT22 sekaligus Participant):\n";
    foreach(array_unique($overlapEmails) as $email) {
        echo "- " . $email . "\n";
    }
}
echo "=========================================\n";
