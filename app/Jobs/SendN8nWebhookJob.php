<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendN8nWebhookJob implements ShouldQueue
{
    use Queueable;

    public $payload;
    public $url;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload, string $url)
    {
        $this->payload = $payload;
        $this->url = $url;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $secretKey = env('N8N_SECRET_KEY');
        
        if (!$this->url) {
            Log::warning('N8N_WEBHOOK_URL is not set in .env');
            return;
        }

        if (!$secretKey) {
            Log::warning('N8N_SECRET_KEY is not set in .env');
            return;
        }

        try {
            Http::timeout(10)
                ->withHeaders([
                    'X-Secret-Key' => $secretKey,
                ])
                ->post($this->url, $this->payload);
        } catch (\Exception $e) {
            Log::error('Failed to send webhook to n8n: ' . $e->getMessage());
        }
    }
}
