<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EventTicket;
use App\Models\CompetitionRegistration;
use App\Models\CompetitionMember;

$seederContent = file_get_contents(database_path('seeders/TicketKeluargaDFTSeeder.php'));
preg_match_all("/'email'\s*=>\s*'([^']+)'/", $seederContent, $matches);
$dftEmails = $matches[1];

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
    
    // Cek apakah dia masuk kloter Keluarga DFT
    $isDFT = in_array($user->email, $dftEmails);

    // Cek apakah dia Participant Lomba
    $isParticipant = CompetitionRegistration::where('user_id', $user->id)->exists();
    if (!$isParticipant) {
        $isParticipant = CompetitionMember::where('user_id', $user->id)->exists();
    }
    
    if ($isDFT && $isParticipant) {
        // Khusus untuk yang dobel jabatan (Keluarga DFT + Peserta)
        // Kita pisahkan antara TIKET UTAMA dia (ikut lomba) dan TIKET KELUARGANYA
        if (str_starts_with($ticket->guest_name, 'Keluarga ')) {
            // Ini tiket tamu keluarganya, jadi masuk DFT22 murni
            $categories['DFT22']++;
        } else {
            // Ini tiket pribadinya dia, jadi masuk dobel jabatan
            $categories['DFT22_AND_Participant']++;
            $overlapEmails[] = $user->email;
        }
    } elseif ($isDFT) {
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
