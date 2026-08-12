<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\EventTicket;
use App\Models\CompetitionRegistration;
use App\Models\CompetitionMember;

// Dapatkan daftar email dari seeder secara dinamis biar 100% akurat
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

// Lacak email orang yang rangkap jabatan
$overlapEmails = [];

foreach ($tickets as $ticket) {
    $reg = $ticket->registration;
    $user = $reg ? $reg->user : null;
    
    if (!$user) {
        $categories['GUEST']++;
        continue;
    }
    
    // Cek apakah dia Keluarga DFT berdasarkan email di seeder
    $isDFT = in_array($user->email, $dftEmails);

    // Cek apakah dia Participant Lomba (ada di tabel registrasi sebagai leader ATAU member)
    $isParticipant = CompetitionRegistration::where('user_id', $user->id)->exists();
    if (!$isParticipant) {
        $isParticipant = CompetitionMember::where('user_id', $user->id)->exists();
    }
    
    if ($isDFT && $isParticipant) {
        $categories['DFT22_AND_Participant']++;
        $overlapEmails[] = $user->email;
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
