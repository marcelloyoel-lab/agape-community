<?php

namespace App\Services\WhatsApp;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class BotAuthenticationService
{
    private const AUTH_TTL_MINUTES = 20;

    private const CACHE_PREFIX = 'whatsapp:auth:';

    public function authenticate(string $senderId, string $credentials): bool
    {
        if (! $this->canAttemptLogin($senderId)) {
            return false;
        }

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
            $this->recordFailedLogin($senderId);
            return false;
        }
        
        $this->clearLoginAttempts($senderId);

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

    public function shouldSendAuthPrompt(string $senderId): bool
    {
        $key = $this->authPromptKey($senderId);

        if (RateLimiter::tooManyAttempts($key, 1)) {
            return false;
        }

        RateLimiter::hit($key, 30);

        return true;
    }

    public function canAttemptLogin(string $senderId): bool
    {
        return ! RateLimiter::tooManyAttempts(
            $this->loginAttemptKey($senderId),
            3
        );
    }

    public function recordFailedLogin(string $senderId): void
    {
        RateLimiter::hit(
            $this->loginAttemptKey($senderId),
            15 * 60
        );
    }

    public function clearLoginAttempts(string $senderId): void
    {
        RateLimiter::clear($this->loginAttemptKey($senderId));
    }

    private function authPromptKey(string $senderId): string
    {
        return 'whatsapp:auth-prompt:'.hash('sha256', $senderId);
    }

    private function loginAttemptKey(string $senderId): string
    {
        return 'whatsapp:login-attempt:'.hash('sha256', $senderId);
    }
}