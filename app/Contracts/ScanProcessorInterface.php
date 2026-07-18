<?php

namespace App\Contracts;

use App\Models\User;

interface ScanProcessorInterface
{
    /**
     * Process check-in for Admin Scan
     */
    public function processAdminScan(string $id): array;

    /**
     * Process check-in for User Scan (Token based)
     */
    public function processUserScan(User $user, object $payload): array;
}
