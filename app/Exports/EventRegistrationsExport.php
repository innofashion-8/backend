<?php

namespace App\Exports;

use App\Models\EventRegistration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EventRegistrationsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        $registrations = EventRegistration::with(['event', 'user'])
            ->where('status', '!=', 'DRAFT')
            ->get();
        $exportData = [];

        foreach ($registrations as $reg) {
            $mUser = $reg->user;
            
            $idCardLink = '-';
            if ($mUser && $mUser->ktm_path) {
                $idCardLink = url('storage/' . $mUser->ktm_path);
            } elseif ($mUser && $mUser->id_card_path) {
                $idCardLink = url('storage/' . $mUser->id_card_path);
            }

            $exportData[] = [
                'registration_id' => $reg->id ?: '-',
                'event_name' => $reg->event?->title ?: '-',
                'status' => ($reg->status?->value ?? $reg->status) ?: '-',
                'name' => $mUser ? ($mUser->name ?: '-') : '-',
                'email' => $mUser ? ($mUser->email ?: '-') : '-',
                'phone' => $mUser ? ($mUser->phone ?: '-') : '-',
                'line' => $mUser ? ($mUser->line ?: '-') : '-',
                'institution' => $mUser ? ($mUser->institution ?: '-') : '-',
                'nrp' => $mUser ? ($mUser->nrp ?: '-') : '-',
                'major' => $mUser ? ($mUser->major ?: '-') : '-',
                'batch' => $mUser ? ($mUser->batch ?: '-') : '-',
                'id_card_link' => $idCardLink,
            ];
        }

        return collect($exportData);
    }

    public function headings(): array
    {
        return [
            'Registration ID',
            'Event Name',
            'Status',
            'Name',
            'Email',
            'Phone',
            'Line',
            'Institution',
            'NRP',
            'Major',
            'Batch',
            'ID Card / KTM Link',
        ];
    }

    public function map($row): array
    {
        return [
            $row['registration_id'],
            $row['event_name'],
            $row['status'],
            $row['name'],
            $row['email'],
            $row['phone'],
            $row['line'],
            $row['institution'],
            $row['nrp'],
            $row['major'],
            $row['batch'],
            $row['id_card_link'],
        ];
    }
}
