<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return User::all();
    }

    public function map($user): array
    {
        $idCardLink = '-';
        if ($user->ktm_path) {
            $idCardLink = url('storage/' . $user->ktm_path);
        } elseif ($user->id_card_path) {
            $idCardLink = url('storage/' . $user->id_card_path);
        }

        return [
            $user->name ?: '-',
            $user->email ?: '-',
            ($user->type?->value ?? $user->type) ?: '-',
            $user->institution ?: '-',
            $user->nrp ?: '-',
            $user->batch ?: '-',
            $user->major ?: '-',
            $user->phone ?: '-',
            $user->line ?: '-',
            $user->is_profile_complete ? 'Yes' : 'No',
            $idCardLink,
            $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Type',
            'Institution',
            'NRP',
            'Batch',
            'Major',
            'Phone',
            'Line',
            'Register Complete',
            'ID Card / KTM Link',
            'Registered At',
        ];
    }
}
