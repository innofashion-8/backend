<?php

namespace Database\Seeders;

use App\Enum\AttendedStatus;
use App\Enum\CompetitionCategory;
use App\Enum\EventCategory;
use App\Enum\RegionType;
use App\Enum\StatusRegistration;
use App\Enum\UserType;
use App\Models\Competition;
use App\Models\CompetitionMember;
use App\Models\CompetitionRegistration;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ParticipantAndGuestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fashionShow = Event::where('category', EventCategory::FASHION_SHOW)->first();
        if (!$fashionShow) {
            $this->command->error('Fashion Show event not found. Please run EventSeeder first.');
            return;
        }

        $fashionSketch = Competition::where('slug', 'fashion-sketch')->first();
        $restyling = Competition::where('slug', 'restyling')->first();

        $this->command->info('Seeding 30 Participants, 20 Guests, and 10 VVIPs...');

        // ==========================================
        // 1. SEED 30 PESERTA (PARTICIPANTS)
        // ==========================================
        $participantsData = [
            // 15 Internal (PCU Students)
            ['name' => 'Adriel Suryajaya', 'email' => 'c14240001@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'C14240001', 'batch' => 2024, 'major' => 'Informatics', 'institution' => 'Petra Christian University', 'phone' => '081234000001'],
            ['name' => 'Amanda Vania Tan', 'email' => 'e12240002@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'E12240002', 'batch' => 2024, 'major' => 'Desain Fashion & Tekstil', 'institution' => 'Petra Christian University', 'phone' => '081234000002'],
            ['name' => 'Brandon Clarance', 'email' => 'c14240003@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'C14240003', 'batch' => 2024, 'major' => 'Informatics', 'institution' => 'Petra Christian University', 'phone' => '081234000003'],
            ['name' => 'Calista Aurelia', 'email' => 'e12240004@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'E12240004', 'batch' => 2024, 'major' => 'Desain Fashion & Tekstil', 'institution' => 'Petra Christian University', 'phone' => '081234000004'],
            ['name' => 'Darren Alexander', 'email' => 'c14240005@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'C14240005', 'batch' => 2024, 'major' => 'Informatics', 'institution' => 'Petra Christian University', 'phone' => '081234000005'],
            ['name' => 'Elvina Susanto', 'email' => 'e12250006@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'E12250006', 'batch' => 2025, 'major' => 'Desain Fashion & Tekstil', 'institution' => 'Petra Christian University', 'phone' => '081234000006'],
            ['name' => 'Febian Wijaya', 'email' => 'c14250007@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'C14250007', 'batch' => 2025, 'major' => 'Industrial Engineering', 'institution' => 'Petra Christian University', 'phone' => '081234000007'],
            ['name' => 'Grace Natalie', 'email' => 'e12250008@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'E12250008', 'batch' => 2025, 'major' => 'Desain Fashion & Tekstil', 'institution' => 'Petra Christian University', 'phone' => '081234000008'],
            ['name' => 'Hans Christian', 'email' => 'c14230009@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'C14230009', 'batch' => 2023, 'major' => 'Informatics', 'institution' => 'Petra Christian University', 'phone' => '081234000009'],
            ['name' => 'Irene Setiawan', 'email' => 'e12230010@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'E12230010', 'batch' => 2023, 'major' => 'Desain Komunikasi Visual', 'institution' => 'Petra Christian University', 'phone' => '081234000010'],
            ['name' => 'Jason Hartono', 'email' => 'c14230011@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'C14230011', 'batch' => 2023, 'major' => 'Informatics', 'institution' => 'Petra Christian University', 'phone' => '081234000011'],
            ['name' => 'Karen Elizabeth', 'email' => 'e12240012@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'E12240012', 'batch' => 2024, 'major' => 'Desain Fashion & Tekstil', 'institution' => 'Petra Christian University', 'phone' => '081234000012'],
            ['name' => 'Kevin Jonathan', 'email' => 'c14240013@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'C14240013', 'batch' => 2024, 'major' => 'Architecture', 'institution' => 'Petra Christian University', 'phone' => '081234000013'],
            ['name' => 'Livia Anggraini', 'email' => 'e12240014@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'E12240014', 'batch' => 2024, 'major' => 'Desain Fashion & Tekstil', 'institution' => 'Petra Christian University', 'phone' => '081234000014'],
            ['name' => 'Matthew Nicholas', 'email' => 'c14250015@john.petra.ac.id', 'type' => UserType::INTERNAL, 'nrp' => 'C14250015', 'batch' => 2025, 'major' => 'Informatics', 'institution' => 'Petra Christian University', 'phone' => '081234000015'],

            // 15 External
            ['name' => 'Nicole Patricia', 'email' => 'nicole.patricia@gmail.com', 'type' => UserType::EXTERNAL, 'institution' => 'Universitas Ciputra', 'phone' => '085710000016'],
            ['name' => 'Oliver Budiman', 'email' => 'oliver.budiman@yahoo.com', 'type' => UserType::EXTERNAL, 'institution' => 'SMA Negeri 5 Surabaya', 'phone' => '085710000017'],
            ['name' => 'Patricia Winata', 'email' => 'patricia.winata@gmail.com', 'type' => UserType::EXTERNAL, 'institution' => 'SMA Katolik St. Louis 1', 'phone' => '085710000018'],
            ['name' => 'Raymond Santoso', 'email' => 'raymond.santoso@outlook.com', 'type' => UserType::EXTERNAL, 'institution' => 'Universitas Airlangga', 'phone' => '085710000019'],
            ['name' => 'Stephanie Halim', 'email' => 'stephanie.halim@gmail.com', 'type' => UserType::EXTERNAL, 'institution' => 'Universitas Surabaya', 'phone' => '085710000020'],
            ['name' => 'Timothy Gunawan', 'email' => 'timothy.gunawan@gmail.com', 'type' => UserType::EXTERNAL, 'institution' => 'SMA Petra 1 Surabaya', 'phone' => '085710000021'],
            ['name' => 'Vanessa Kurniawan', 'email' => 'vanessa.kurniawan@gmail.com', 'type' => UserType::EXTERNAL, 'institution' => 'Institut Teknologi Sepuluh Nopember', 'phone' => '085710000022'],
            ['name' => 'William Chandra', 'email' => 'william.chandra@gmail.com', 'type' => UserType::EXTERNAL, 'institution' => 'SMA Katolik Santa Maria', 'phone' => '085710000023'],
            ['name' => 'Yvonne Sugiarto', 'email' => 'yvonne.sugiarto@gmail.com', 'type' => UserType::EXTERNAL, 'institution' => 'Universitas Ciputra', 'phone' => '085710000024'],
            ['name' => 'Zachariah Pratama', 'email' => 'zachariah.pratama@gmail.com', 'type' => UserType::EXTERNAL, 'institution' => 'SMA Negeri 2 Surabaya', 'phone' => '085710000025'],
            ['name' => 'Jessica Mulyadi', 'email' => 'jessica.mulyadi@gmail.com', 'type' => UserType::EXTERNAL, 'institution' => 'SMA Petra 2 Surabaya', 'phone' => '085710000026'],
            ['name' => 'Nicholas Lie', 'email' => 'nicholas.lie@gmail.com', 'type' => UserType::EXTERNAL, 'institution' => 'Universitas Brawijaya', 'phone' => '085710000027'],
            ['name' => 'Rebecca Kusuma', 'email' => 'rebecca.kusuma@gmail.com', 'type' => UserType::EXTERNAL, 'institution' => 'SMA Katolik St. Louis 1', 'phone' => '085710000028'],
            ['name' => 'Daniel Handojo', 'email' => 'daniel.handojo@gmail.com', 'type' => UserType::EXTERNAL, 'institution' => 'SMA Negeri 1 Surabaya', 'phone' => '085710000029'],
            ['name' => 'Michelle Pangestu', 'email' => 'michelle.pangestu@gmail.com', 'type' => UserType::EXTERNAL, 'institution' => 'Universitas Pelita Harapan', 'phone' => '085710000030'],
        ];

        $participantUsers = [];

        foreach ($participantsData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'                => $data['name'],
                    'type'                => $data['type'],
                    'nrp'                 => $data['nrp'] ?? null,
                    'batch'               => $data['batch'] ?? null,
                    'major'               => $data['major'] ?? null,
                    'institution'         => $data['institution'],
                    'phone'               => $data['phone'],
                    'line'                => strtolower(str_replace(' ', '', $data['name'])),
                    'is_profile_complete' => true,
                ]
            );

            $participantUsers[] = $user;

            // Register Event Fashion Show
            $eventReg = EventRegistration::firstOrCreate(
                ['user_id' => $user->id, 'event_id' => $fashionShow->id],
                ['status' => StatusRegistration::VERIFIED->value]
            );

            // Create Event Ticket
            $ticketCode = 'TIX-P-' . strtoupper(Str::random(6));
            while (EventTicket::where('ticket_code', $ticketCode)->exists()) {
                $ticketCode = 'TIX-P-' . strtoupper(Str::random(6));
            }

            EventTicket::firstOrCreate(
                ['event_registration_id' => $eventReg->id, 'guest_name' => $user->name],
                ['ticket_code' => $ticketCode, 'attended_status' => AttendedStatus::PENDING->value]
            );
        }

        // Optional: Register 10 participants to Fashion Sketch Competition
        if ($fashionSketch) {
            for ($i = 0; $i < 10; $i++) {
                if (isset($participantUsers[$i])) {
                    CompetitionRegistration::firstOrCreate(
                        ['user_id' => $participantUsers[$i]->id, 'competition_id' => $fashionSketch->id],
                        [
                            'category' => CompetitionCategory::ADVANCED->value,
                            'region'   => RegionType::NATIONAL->value,
                            'status'   => StatusRegistration::VERIFIED->value,
                        ]
                    );
                }
            }
        }

        // Optional: Register 10 participants to Restyling Competition (Groups of 2)
        if ($restyling) {
            for ($i = 10; $i < 20; $i += 2) {
                if (isset($participantUsers[$i]) && isset($participantUsers[$i + 1])) {
                    $leader = $participantUsers[$i];
                    $member = $participantUsers[$i + 1];

                    $groupReg = CompetitionRegistration::firstOrCreate(
                        ['user_id' => $leader->id, 'competition_id' => $restyling->id],
                        [
                            'group_name' => 'Team Restyling ' . ($i / 2),
                            'category'   => CompetitionCategory::INTERMEDIATE->value,
                            'region'     => RegionType::NATIONAL->value,
                            'status'     => StatusRegistration::VERIFIED->value,
                        ]
                    );

                    CompetitionMember::firstOrCreate(
                        ['registration_id' => $groupReg->id, 'user_id' => $member->id],
                        ['member_order' => 1]
                    );
                }
            }
        }


        // ==========================================
        // 2. SEED 20 GUEST (TAMU UMUM)
        // ==========================================
        $guestsData = [
            ['name' => 'Agus Budiman', 'email' => 'agus.budiman@gmail.com', 'phone' => '081390000001'],
            ['name' => 'Bambang Suherman', 'email' => 'bambang.suherman@gmail.com', 'phone' => '081390000002'],
            ['name' => 'Christine Wibowo', 'email' => 'christine.wibowo@gmail.com', 'phone' => '081390000003'],
            ['name' => 'Dian Permata', 'email' => 'dian.permata@gmail.com', 'phone' => '081390000004'],
            ['name' => 'Eko Prasetyo', 'email' => 'eko.prasetyo@gmail.com', 'phone' => '081390000005'],
            ['name' => 'Fitri Handayani', 'email' => 'fitri.handayani@gmail.com', 'phone' => '081390000006'],
            ['name' => 'Gunawan Kartiko', 'email' => 'gunawan.kartiko@gmail.com', 'phone' => '081390000007'],
            ['name' => 'Hendra Saputra', 'email' => 'hendra.saputra@gmail.com', 'phone' => '081390000008'],
            ['name' => 'Indah Lestari', 'email' => 'indah.lestari@gmail.com', 'phone' => '081390000009'],
            ['name' => 'Joko Widodo', 'email' => 'joko.widodo@gmail.com', 'phone' => '081390000010'],
            ['name' => 'Kartika Dewi', 'email' => 'kartika.dewi@gmail.com', 'phone' => '081390000011'],
            ['name' => 'Lukman Hakim', 'email' => 'lukman.hakim@gmail.com', 'phone' => '081390000012'],
            ['name' => 'Maya Safitri', 'email' => 'maya.safitri@gmail.com', 'phone' => '081390000013'],
            ['name' => 'Nurul Hidayah', 'email' => 'nurul.hidayah@gmail.com', 'phone' => '081390000014'],
            ['name' => 'Oscar Rinaldi', 'email' => 'oscar.rinaldi@gmail.com', 'phone' => '081390000015'],
            ['name' => 'Putri Utami', 'email' => 'putri.utami@gmail.com', 'phone' => '081390000016'],
            ['name' => 'Rahmat Hidayat', 'email' => 'rahmat.hidayat@gmail.com', 'phone' => '081390000017'],
            ['name' => 'Sri Rahayu', 'email' => 'sri.rahayu@gmail.com', 'phone' => '081390000018'],
            ['name' => 'Taufik Hidayat', 'email' => 'taufik.hidayat@gmail.com', 'phone' => '081390000019'],
            ['name' => 'Yulia Ningsih', 'email' => 'yulia.ningsih@gmail.com', 'phone' => '081390000020'],
        ];

        foreach ($guestsData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'                => $data['name'],
                    'type'                => UserType::GUEST,
                    'institution'         => 'General Public',
                    'phone'               => $data['phone'],
                    'line'                => strtolower(str_replace(' ', '', $data['name'])),
                    'is_profile_complete' => true,
                ]
            );

            $eventReg = EventRegistration::firstOrCreate(
                ['user_id' => $user->id, 'event_id' => $fashionShow->id],
                ['status' => StatusRegistration::VERIFIED->value]
            );

            $ticketCode = 'TIX-G-' . strtoupper(Str::random(6));
            while (EventTicket::where('ticket_code', $ticketCode)->exists()) {
                $ticketCode = 'TIX-G-' . strtoupper(Str::random(6));
            }

            EventTicket::firstOrCreate(
                ['event_registration_id' => $eventReg->id, 'guest_name' => $user->name],
                ['ticket_code' => $ticketCode, 'attended_status' => AttendedStatus::PENDING->value]
            );
        }


        // ==========================================
        // 3. SEED 10 VVIP GUESTS (TAMU VVIP)
        // ==========================================
        $vvipsData = [
            ['name' => 'Prof. Dr. Ir. Herman Tanudjaja', 'email' => 'herman.tanudjaja@vvip.com', 'phone' => '081190000001'],
            ['name' => 'Dr. Dra. Grace S. Tanudjaja, M.Des.', 'email' => 'grace.tanudjaja@vvip.com', 'phone' => '081190000002'],
            ['name' => 'Ir. Budi Hartono, M.T.', 'email' => 'budi.hartono@vvip.com', 'phone' => '081190000003'],
            ['name' => 'Christian Kusuma', 'email' => 'christian.kusuma@vvip.com', 'phone' => '081190000004'],
            ['name' => 'Stephanie Wibisono', 'email' => 'stephanie.wibisono@vvip.com', 'phone' => '081190000005'],
            ['name' => 'Dra. Agnes Triana, M.Hum.', 'email' => 'agnes.triana@vvip.com', 'phone' => '081190000006'],
            ['name' => 'Dr. Raymond Sugiarto', 'email' => 'raymond.sugiarto@vvip.com', 'phone' => '081190000007'],
            ['name' => 'Vice Rector Student Affairs PCU', 'email' => 'vice.rector@vvip.com', 'phone' => '081190000008'],
            ['name' => 'Senior Fashion Curator Surabaya', 'email' => 'curator@vvip.com', 'phone' => '081190000009'],
            ['name' => 'Chief Editor Style Magazine', 'email' => 'editor@vvip.com', 'phone' => '081190000010'],
        ];

        foreach ($vvipsData as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'                => $data['name'],
                    'type'                => UserType::GUEST,
                    'institution'         => 'VVIP Guest',
                    'phone'               => $data['phone'],
                    'line'                => strtolower(str_replace(' ', '', $data['name'])),
                    'is_profile_complete' => true,
                ]
            );

            $eventReg = EventRegistration::firstOrCreate(
                ['user_id' => $user->id, 'event_id' => $fashionShow->id],
                ['status' => StatusRegistration::VERIFIED->value]
            );

            $ticketCode = 'TIX-VVIP-' . strtoupper(Str::random(6));
            while (EventTicket::where('ticket_code', $ticketCode)->exists()) {
                $ticketCode = 'TIX-VVIP-' . strtoupper(Str::random(6));
            }

            EventTicket::firstOrCreate(
                ['event_registration_id' => $eventReg->id],
                [
                    'guest_name'      => 'VVIP - ' . $user->name,
                    'ticket_code'     => $ticketCode,
                    'attended_status' => AttendedStatus::PENDING->value
                ]
            );
        }

        $this->command->info('Successfully seeded 30 Participants, 20 Guests, and 10 VVIPs!');
    }
}
