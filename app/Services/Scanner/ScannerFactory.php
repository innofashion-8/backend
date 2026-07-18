<?php

namespace App\Services\Scanner;

use App\Contracts\ScanProcessorInterface;
use Illuminate\Validation\ValidationException;

class ScannerFactory
{
    public static function make(string $type): ScanProcessorInterface
    {
        return match ($type) {
            'event' => new EventScanProcessor(),
            'ticket' => new TicketScanProcessor(),
            default => throw ValidationException::withMessages([
                'type' => ['Tipe Scanner tidak dikenali: ' . $type]
            ]),
        };
    }
}
