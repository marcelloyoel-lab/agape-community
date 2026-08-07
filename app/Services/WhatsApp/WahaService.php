<?php

namespace App\Services\WhatsApp;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\ConnectionException;
use RuntimeException;

class WahaService
{
    private string $baseUrl;

    private string $apiKey;

    private string $session;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.waha.base_url'), '/');
        $this->apiKey = (string) config('services.waha.api_key');
        $this->session = (string) config('services.waha.session', 'default');

        if ($this->baseUrl === '' || $this->apiKey === '') {
            throw new RuntimeException('WAHA configuration is incomplete.');
        }
    }

    public function groupId(): string
    {
        return (string) config('services.waha.group_id');
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->connectTimeout(3)
            ->timeout(10);
    }

    public function health(): array
    {
        try {
            $response = $this->client()
                ->get('/api/server/status')
                ->throw();

            Log::info('WAHA health check successful.', [
                'status_code' => $response->status(),
            ]);

            return $response->json() ?? [];
        } catch (ConnectionException|RequestException $exception) {
            Log::error('WAHA health check failed.', [
                'status_code' => $exception instanceof RequestException
                    ? $exception->response?->status()
                    : null,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function sendText(string $chatId, string $text): array
    {
        try {
            $response = $this->client()
                ->post('/api/sendText', [
                    'session' => $this->session,
                    'chatId' => $chatId,
                    'text' => $text,
                ])
                ->throw();

            Log::info('WAHA text message sent.', [
                'chat_id' => $chatId,
                'status_code' => $response->status(),
            ]);

            return $response->json() ?? [];
        } catch (ConnectionException|RequestException $exception) {
            Log::error('WAHA text message failed.', [
                'chat_id' => $chatId,
                'status_code' => $exception instanceof RequestException
                    ? $exception->response?->status()
                    : null,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function sendImage(
        string $chatId,
        string $imageUrl,
        string $filename,
        ?string $caption = null
    ): array {
        Log::info('Sending WhatsApp image.', [
            'chat_id' => $chatId,
            'image_url' => $imageUrl,
        ]);
        try {
            Log::info('Calling WAHA /sendImage.');
            $response = $this->mediaClient()
                ->post('/api/sendImage', [
                    'session' => $this->session,
                    'chatId' => $chatId,
                    'file' => [
                        'mimetype' => 'image/jpeg',
                        'url' => $imageUrl,
                        'filename' => $filename,
                    ],
                    'caption' => $caption,
                ])
                ->throw();

            Log::info('WAHA image message sent.', [
                'chat_id' => $chatId,
                'status_code' => $response->status(),
            ]);

            return $response->json() ?? [];
        } catch (ConnectionException|RequestException $exception) {
            Log::error('WAHA image message failed.', [
                'chat_id' => $chatId,
                'status_code' => $exception instanceof RequestException
                    ? $exception->response?->status()
                    : null,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function sendImageToGroup(
        string $imageUrl,
        string $filename,
        ?string $caption = null
    ): array
    {
        $groupId = $this->groupId();

        if (blank($groupId)) {
            throw new RuntimeException('WhatsApp group ID is not configured.');
        }

        Log::info('Sending WhatsApp image to church group.', [
            'group_id' => $groupId,
        ]);

        return $this->sendImage(
            $groupId,
            $imageUrl,
            $filename,
            $caption
        );
    }

    private function mediaClient(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withHeaders([
                'X-Api-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->connectTimeout(5)
            ->timeout(60);
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function groups(): array
    {
        try {
            $response = $this->client()
                ->get('/api/chats', [
                    'session' => $this->session,
                ])
                ->throw();

            Log::info('WAHA groups retrieved successfully.', [
                'status_code' => $response->status(),
            ]);

            return collect($response->json() ?? [])
                ->filter(fn (array $chat) => str_ends_with($chat['id'] ?? '', '@g.us'))
                ->values()
                ->all();

        } catch (ConnectionException|RequestException $exception) {
            Log::error('Failed to retrieve WhatsApp groups.', [
                'status_code' => $exception instanceof RequestException
                    ? $exception->response?->status()
                    : null,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    // Untuk testing
    public function testGroups(): array
    {
        return $this->client()
            ->get("/api/{$this->session}/groups")
            ->throw()
            ->json();
    }

    /**
     * @throws ConnectionException
     * @throws RequestException
     */
    public function groupList(): array
    {
        try {
            $response = $this->client()
                ->get("/api/{$this->session}/groups")
                ->throw();

            Log::info('WAHA groups retrieved successfully.', [
                'status_code' => $response->status(),
            ]);

            return collect($response->json() ?? [])
            ->map(
                fn (array $group) => [
                    'name' => $group['name'],
                    'id' => $group['id']['_serialized'],
                ]
            )
            ->values()
            ->all();

        } catch (ConnectionException|RequestException $exception) {
            Log::error('Failed to retrieve WhatsApp groups.', [
                'status_code' => $exception instanceof RequestException
                    ? $exception->response?->status()
                    : null,
                'message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    public function isGroupChat(string $chatId): bool
    {
        return str_ends_with($chatId, '@g.us');
    }
}