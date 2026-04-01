<?php

namespace App\Services;

class TokenService
{
    public function storeSession(string $token, array $user): void
    {
        session(['cloud_token' => $token, 'cloud_user' => $user]);
    }

    public function getToken(): ?string
    {
        return session('cloud_token');
    }

    public function getUser(): ?array
    {
        return session('cloud_user');
    }

    public function hasSession(): bool
    {
        return session()->has('cloud_token');
    }

    public function clear(): void
    {
        session()->forget(['cloud_token', 'cloud_user']);
    }
}
