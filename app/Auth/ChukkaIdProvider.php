<?php

namespace App\Auth;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User;

class ChukkaIdProvider extends AbstractProvider
{
    protected $scopes = [''];

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            config('services.chukka_id.url').'/oauth/authorize',
            $state,
        );
    }

    protected function getTokenUrl(): string
    {
        return config('services.chukka_id.url').'/oauth/token';
    }

    /** @return array<string, mixed> */
    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get(
            config('services.chukka_id.url').'/api/user',
            [
                'headers' => [
                    'Authorization' => 'Bearer '.$token,
                    'Accept' => 'application/json',
                ],
            ],
        );

        return json_decode((string) $response->getBody(), true);
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
        ]);
    }
}
