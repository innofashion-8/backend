<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EventTicket;
use App\Models\CompetitionRegistration;
use App\Models\CompetitionMember;
use App\Enum\TicketCategory;
use Illuminate\Support\Facades\DB;

class SyncTicketCategoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:sync-category';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncs ticket_category for all existing event tickets.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai analisa tiket...");

        $participantUserIds = collect();
        $participantUserIds = $participantUserIds->merge(CompetitionRegistration::pluck('user_id'));
        $participantUserIds = $participantUserIds->merge(CompetitionMember::pluck('user_id'));
        $participantUserIds = $participantUserIds->unique()->toArray();

        $keluargaRegIds = EventTicket::where('guest_name', 'LIKE', 'Keluarga %')
            ->pluck('event_registration_id')
            ->unique()
            ->toArray();

        $tickets = EventTicket::with(['registration.user'])->get();

        $categories = [
            'GUEST' => 0,
            'DFT22' => 0,
            'PARTICIPANT' => 0,
        ];
        
        $updates = [];

        foreach ($tickets as $ticket) {
            $reg = $ticket->registration;
            $user = $reg ? $reg->user : null;
            
            if (!$user) {
                $categories['GUEST']++;
                $updates[$ticket->id] = TicketCategory::GUEST->value;
                continue;
            }

            $isParticipant = in_array($user->id, $participantUserIds);
            $isKeluarga = in_array($reg->id, $keluargaRegIds);
            
            $category = TicketCategory::GUEST->value;

            if ($isKeluarga) {
                // Semua tiket keluarga DFT (baik utama maupun tamu) dihitung sebagai dft22 sesuai kesepakatan terbaru
                $category = TicketCategory::DFT22->value;
                $categories['DFT22']++;
            } elseif ($isParticipant) {
                $category = TicketCategory::COMPETITION_PARTICIPANT->value;
                $categories['PARTICIPANT']++;
            } else {
                $category = TicketCategory::GUEST->value;
                $categories['GUEST']++;
            }
            
            $updates[$ticket->id] = $category;
        }

        $this->info("=========================================");
        $this->info("       HASIL ANALISA TIKET EVENT         ");
        $this->info("=========================================");
        $this->info("Total Tiket di Database : " . $tickets->count());
        $this->info("- GUEST                 : " . $categories['GUEST'] . " tiket");
        $this->info("- DFT22 (Keluarga)      : " . $categories['DFT22'] . " tiket");
        $this->info("- PARTICIPANT (Lomba)   : " . $categories['PARTICIPANT'] . " tiket");
        $this->info("=========================================\n");

        if ($this->confirm('Apakah Anda ingin memproses (sync) perubahan ini ke database sekarang?')) {
            $this->info("Menyimpan ke database...");
            DB::transaction(function () use ($updates) {
                foreach ($updates as $ticketId => $category) {
                    EventTicket::where('id', $ticketId)->update(['ticket_category' => $category]);
                }
            });
            $this->info("Proses sync selesai! {$tickets->count()} tiket berhasil diperbarui.");
        } else {
            $this->info("Proses dibatalkan.");
        }
    }
}
