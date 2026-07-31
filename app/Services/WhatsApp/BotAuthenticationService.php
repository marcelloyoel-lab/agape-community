<?php

namespace App\Services\WhatsApp;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class BotAuthenticationService
{
    private const AUTH_TTL_MINUTES = 20;

    private const CACHE_PREFIX = 'whatsapp:auth:';

    public function authenticate(string $senderId, string $credentials): bool
    {
        $credentials = trim($credentials);

        if (! str_contains($credentials, '|')) {
            return false;
        }

        [$email, $password] = explode('|', $credentials, 2);

        $email = trim($email);

        if ($email === '' || $password === '') {
            return false;
        }

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return false;
        }

        Cache::put(
            $this->cacheKey($senderId),
            $user->id,
            now()->addMinutes(self::AUTH_TTL_MINUTES)
        );

        return true;
    }

    public function isAuthenticated(string $senderId): bool
    {
        return Cache::has($this->cacheKey($senderId));
    }

    public function user(string $senderId): ?User
    {
        $userId = Cache::get($this->cacheKey($senderId));

        if (! $userId) {
            return null;
        }

        return User::find($userId);
    }

    private function cacheKey(string $senderId): string
    {
        return self::CACHE_PREFIX.hash('sha256', $senderId);
    }
}