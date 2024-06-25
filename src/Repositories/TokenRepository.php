<?php

namespace XCoorp\PassportControl\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;
use XCoorp\PassportControl\PassportControl;
use XCoorp\PassportControl\Token;

class TokenRepository
{
    /**
     * Get a token by the given ID.
     */
    public function introspect(string $token_id): ?Token
    {
        $at = $this->getAccessToken();
        if (! $at) {
            return null;
        }

        try {
            $atResponse = Http::retry(3, 1000)
                ->withToken($at)
                ->withHeader('Accept', 'application/json')
                ->asForm()
                ->post(PassportControl::introspectEndpoint(), [
                    'token' => $token_id,
                    'token_type_hint' => 'access_token',
                ]);

            if ($atResponse->failed()) {
                return null;
            }

            $json = $atResponse->json();
            if (! $json) {
                return null;
            }

            return new Token(
                $json['active'] ?? false,
                isset($json['scope']) ? explode(' ', $json['scope']) : [],
                $json['client_id'],
                $json['username'],
                $json['exp']
            );
        } catch (Throwable) {
        }

        return null;
    }

    /**
     * This is used by the League OAuth2 server to validate the access token.
     * Since we call the introspect method to get the token details, we don't need to implement this method.
     * If an access token is expired, we know from the introspect endpoint.
     *
     * @return false
     */
    public function isAccessTokenRevoked(string $token_id): bool
    {
        return false;
    }

    /**
     * The introspection API is not a publicly accessible endpoint.
     * In order to access it, we need to authenticate with the introspection API.
     * This method returns the access token that we use to authenticate with the introspection API.
     *
     * It will first try to fetch one from cache, if it does not exist, it will request a new one and store
     * it in the cache.
     */
    public function getAccessToken(): ?string
    {
        // We have this in a separate try-catch block, because we do not want to stop working if the cache fails.
        try {
            $accessToken = Cache::store(PassportControl::cacheStore())->get(PassportControl::cachePrefix().'introspection_at');
            if ($accessToken) {
                return $accessToken;
            }
        } catch (Throwable) {
        }

        try {
            $atResponse = Http::retry(3, 1000)
                ->withHeader('Accept', 'application/json')
                ->asJson()
                ->post(PassportControl::accessTokenEndpoint(), [
                    'grant_type' => 'client_credentials',
                    'client_id' => PassportControl::clientID(),
                    'client_secret' => PassportControl::clientSecret(),
                    'scope' => 'introspect',
                ]);

            if ($atResponse->failed()) {
                return null;
            }

            $json = $atResponse->json();
            if (! $json || ! isset($json['access_token']) || ! isset($json['expires_in'])) {
                return null;
            }

            $accessToken = $json['access_token'];
        } catch (Throwable) {
            return null;
        }

        // We have this in a separate try-catch block, because we do not want to stop working if the cache fails.
        try {
            Cache::store(PassportControl::cacheStore())->put(
                PassportControl::cachePrefix().'introspection_at',
                $json['access_token'],
                $json['expires_in'] - 60
            );
        } catch (Throwable) {
        }

        return $accessToken;
    }
}
