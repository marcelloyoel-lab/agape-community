<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Cache;

class WebhookDeduplicationService
{
    private const TTL_MINUTES = 10;

    public function alreadyProcessed(string $messageId): bool
    {
        return ! Cache::add(
            $this->cacheKey($messageId),
            true,
            now()->addMinutes(self::TTL_MINUTES)
        );
    }

    private function cacheKey(string $messageId): string
    {
        return 'whatsapp:webhook:'.hash('sha256', $messageId);
    }
}