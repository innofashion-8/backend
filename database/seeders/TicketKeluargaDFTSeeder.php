<?php

namespace Database\Seeders;

use App\Enum\EventCategory;
use App\Enum\StatusRegistration;
use App\Enum\UserType;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventTicket;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class TicketKeluargaDFTSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ticketKeluargaDFT = [
            [
                'user' => [
                    'name'        => 'Amilatul Maysharoh',
                    'email'       => 'e12210244@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567890',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Brigitta Kayla Santosa',
                    'email'       => 'e12220103@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567891',
                ],
                'tickets' => [
                    'guest_count' => 3,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Chaterine Putri Halim',
                    'email'       => 'e12210237@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567892',
                ],
                'tickets' => [
                    'guest_count' => 4,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Christine Margaretha',
                    'email'       => 'e12220109@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567893',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Claudia Catherine Sipasultan',
                    'email'       => 'e12220225@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567894',
                ],
                'tickets' => [
                    'guest_count' => 2,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Cynthia Clarissa',
                    'email'       => 'e12220119@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567895',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Diandra Frantia Atmajaya',
                    'email'       => 'e12220011@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567896',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Eunica Shareen Dellamanda',
                    'email'       => 'e12220088@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567897',
                ],
                'tickets' => [
                    'guest_count' => 3,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Evelyn Graciella',
                    'email'       => 'e12220090@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567898',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Febe Maureen Purwanto',
                    'email'       => 'e12220271@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567899',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Gabriella Christy Gautama',
                    'email'       => 'e12220211@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567900',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Jesica Yuliani',
                    'email'       => 'e12220213@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567901',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Jessica Vanessa Putri',
                    'email'       => 'e12210117@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567902',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Kathleen Diandra Muljanto',
                    'email'       => 'e12220164@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567903',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Loveina',
                    'email'       => 'e12220221@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567904',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Melody Putri Jessya',
                    'email'       => 'unknown.melody@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567905',
                ],
                'tickets' => [
                    'guest_count' => 0,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Moza Thea',
                    'email'       => 'e12220174@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567906',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Nadia Florence',
                    'email'       => 'unknown.nadia@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567907',
                ],
                'tickets' => [
                    'guest_count' => 0,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Naomi Hannah',
                    'email'       => 'e12220102@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567908',
                ],
                'tickets' => [
                    'guest_count' => 3,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Nikita Alessandra',
                    'email'       => 'e12220017@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567909',
                ],
                'tickets' => [
                    'guest_count' => 1,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Starla Amanda Wannardi',
                    'email'       => 'e12220150@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567910',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Sthefanie Natajaya',
                    'email'       => 'e12220094@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567911',
                ],
                'tickets' => [
                    'guest_count' => 5,
                ]
            ],
            [
                'user' => [
                    'name'        => 'Nadia Florence',
                    'email'       => 'e12220025@john.petra.ac.id',
                    'type'        => UserType::INTERNAL,
                    'institution' => 'Petra Christian University',
                    'phone'       => '081234567912',
                ],
                'tickets' => [
                    'guest_count' => 2,
                ]
            ],
        ];

        $eventFashionShow = Event::where('category', EventCategory::FASHION_SHOW)->first();
        if (!$eventFashionShow) {
            $this->command->error('Event Fashion Show not found. Please run EventSeeder first.');
            return;
        }

        foreach ($ticketKeluargaDFT as $data) {
            $emailPrefix = explode('@', $data['user']['email'])[0];
            if (strlen($emailPrefix) >= 5) {
                $nrp = strtoupper($emailPrefix);
                
                $batchCode = substr($nrp, 3, 2); 
                
                if (is_numeric($batchCode)) {
                    $batch = (int) ("20" . $batchCode);
                }
            }
            $user = User::firstOrCreate(
                ['email' => $data['user']['email']],
                [
                    'name'        => $data['user']['name'],
                    'type'        => $data['user']['type'],
                    'institution' => $data['user']['institution'],
                    'phone'       => $data['user']['phone'],
                    'major'       => 'DFT',
                    'nrp'         => $nrp ?? null,
                    'batch'       => $batch ?? null,
                    'is_profile_complete' => true,
                ]
            );

            $eventRegistration = EventRegistration::firstOrCreate(
                ['user_id' => $user->id, 'event_id' => $eventFashionShow->id],
                [
                    'status'     => StatusRegistration::PENDING->value, // Sengaja dibuat PENDING agar bisa trigger email manual via web
                ]
            );

            $existingMainTicket = EventTicket::where('event_registration_id', $eventRegistration->id)
                ->where('guest_name', $user->name)
                ->first();

            if (!$existingMainTicket) {
                EventTicket::create([
                    'event_registration_id' => $eventRegistration->id,
                    'guest_name'          => $user->name,
                    'ticket_code'         => 'TIX-' . strtoupper(Str::random(8)),
                ]);
            }

            for ($i = 1; $i <= $data['tickets']['guest_count']; $i++) {
                $guestName = "Keluarga " . $user->name . " " . $i;
                
                $existingTicket = EventTicket::where('event_registration_id', $eventRegistration->id)
                    ->where('guest_name', $guestName)
                    ->first();

                if (!$existingTicket) {
                    $ticketCode = 'TIX-' . strtoupper(Str::random(8));
                    
                    while (EventTicket::where('ticket_code', $ticketCode)->exists()) {
                        $ticketCode = 'TIX-' . strtoupper(Str::random(8));
                    }

                    EventTicket::create([
                        'event_registration_id' => $eventRegistration->id,
                        'guest_name'          => $guestName,
                        'ticket_code'         => $ticketCode,
                    ]);
                }
            }
        }
    }
}
