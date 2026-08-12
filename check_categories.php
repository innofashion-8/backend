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

// 2. SET KELUARGA REGISTRATIONS (Ciri khas: Punya minimal 1 tiket bernama awalan "Keluarga ")
// Jika punya tiket ini, MAKA SELURUH TIKET dalam registration_id yang sama adalah kloter VIP.
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

// Array untuk menyimpan daftar nama di setiap kategori
$names = [
    'GUEST' => [],
    'DFT22' => [],
    'Participant' => [],
    'DFT22_AND_Participant' => [],
];

foreach ($tickets as $ticket) {
    $reg = $ticket->registration;
    $user = $reg ? $reg->user : null;
    
    if (!$user) {
        $categories['GUEST']++;
        $names['GUEST'][] = $ticket->guest_name . " (NO USER)";
        continue;
    }

    $isParticipant = in_array($user->id, $participantUserIds);
    $isKeluarga = in_array($reg->id, $keluargaRegIds);
    
    $guestName = $ticket->guest_name;

    if ($isKeluarga && $isParticipant) {
        // Logika Prioritas:
        // Cuma tiket utama yang dihitung dobel jabatan, tiket tamunya murni VIP
        if ($guestName === $user->name) {
            $categories['DFT22_AND_Participant']++;
            $names['DFT22_AND_Participant'][] = $guestName . " (" . $user->email . ")";
        } else {
            $categories['DFT22']++;
            $names['DFT22'][] = $guestName;
        }
    } elseif ($isKeluarga) {
        $categories['DFT22']++;
        $names['DFT22'][] = $guestName;
    } elseif ($isParticipant) {
        $categories['Participant']++;
        $names['Participant'][] = $guestName;
    } else {
        $categories['GUEST']++;
        $names['GUEST'][] = $guestName;
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
echo "- DFT22 + PARTICIPANT     : " . $categories['DFT22_AND_Participant'] . " tiket\n\n";

echo "=========================================\n";
echo "       DAFTAR NAMA PER KATEGORI          \n";
echo "=========================================\n";

echo "\n--- [1] KATEGORI: DFT22 + PARTICIPANT (Dobel Jabatan) [" . count($names['DFT22_AND_Participant']) . "] ---\n";
foreach($names['DFT22_AND_Participant'] as $name) {
    echo "- " . $name . "\n";
}

echo "\n--- [2] KATEGORI: PARTICIPANT (Peserta Lomba) [" . count($names['Participant']) . "] ---\n";
foreach($names['Participant'] as $name) {
    echo "- " . $name . "\n";
}

echo "\n--- [3] KATEGORI: DFT22 (Keluarga VIP) [" . count($names['DFT22']) . "] ---\n";
foreach($names['DFT22'] as $name) {
    echo "- " . $name . "\n";
}

echo "\n--- [4] KATEGORI: GUEST (Tamu Umum) [" . count($names['GUEST']) . "] ---\n";
foreach($names['GUEST'] as $name) {
    echo "- " . $name . "\n";
}
echo "=========================================\n";
