<?php

namespace App\Services;

class TokenService
{
    private ?string $runtimeToken = null;

    public function storeSession(string $token, array $user): void
    {
        session(['cloud_token' => $token, 'cloud_user' => $user]);
    }

    public function setToken(string $token): void
    {
        $this->runtimeToken = $token;
    }

    public function getToken(): ?string
    {
        return $this->runtimeToken ?? session('cloud_token');
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
        $this->runtimeToken = null;
        session()->forget(['cloud_token', 'cloud_user']);
    }
}
