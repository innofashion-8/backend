<?php

namespace App\Exports;

use App\Enum\ParticipantType;
use App\Enum\StatusRegistration;
use App\Models\CompetitionRegistration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CompetitionRegistrationsExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        $registrations = CompetitionRegistration::with(['competition', 'user', 'members.user'])
            ->where('status', '!=', StatusRegistration::DRAFT)
            ->get();
        $exportData = [];

        foreach ($registrations as $reg) {
            if ($reg->members && $reg->members->count() > 0) {
                foreach ($reg->members as $member) {
                    $mUser = $member->user;
                    $idCardLink = '-';
                    if ($mUser && $mUser->ktm_path) {
                        $idCardLink = url('storage/' . $mUser->ktm_path);
                    } elseif ($mUser && $mUser->id_card_path) {
                        $idCardLink = url('storage/' . $mUser->id_card_path);
                    }

                    $exportData[] = [
                        'competition_name' => $reg->competition?->name ?: '-',
                        'group_name' => $reg->group_name ?: '-',
                        'region' => ($reg->region?->value ?? $reg->region) ?: '-',
                        'category' => ($reg->category?->value ?? $reg->category) ?: '-',
                        'status' => ($reg->status?->value ?? $reg->status) ?: '-',
                        'member_order' => ($reg->competition?->participant_type === 'INDIVIDUAL' || $reg->competition?->participant_type === ParticipantType::INDIVIDUAL) ? 'Single' : ($member->member_order == 1 ? 'Leader' : 'Member ' . $member->member_order),
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
            } else {
                $mUser = $reg->user;
                $idCardLink = '-';
                if ($mUser && $mUser->ktm_path) {
                    $idCardLink = url('storage/' . $mUser->ktm_path);
                } elseif ($mUser && $mUser->id_card_path) {
                    $idCardLink = url('storage/' . $mUser->id_card_path);
                }

                $exportData[] = [
                    'competition_name' => $reg->competition?->name ?: '-',
                    'group_name' => $reg->group_name ?: '-',
                    'region' => ($reg->region?->value ?? $reg->region) ?: '-',
                    'category' => ($reg->category?->value ?? $reg->category) ?: '-',
                    'status' => ($reg->status?->value ?? $reg->status) ?: '-',
                    'member_order' => 'Single',
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
        }

        return collect($exportData);
    }

    public function headings(): array
    {
        return [
            'Competition Name',
            'Group Name',
            'Region',
            'Category',
            'Status',
            'Role',
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
            $row['competition_name'],
            $row['group_name'],
            $row['region'],
            $row['category'],
            $row['status'],
            $row['member_order'],
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
