<?php

declare(strict_types=1);

namespace XCoorp\PassportControl\Clients;

use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Throwable;
use XCoorp\PassportControl\Contracts\Client as ClientContract;
use XCoorp\PassportControl\PassportControl;

class AuthServerClient implements ClientContract
{
    /**
     * {@inheritDoc}
     */
    public function request(): PendingRequest|Factory|null
    {
        $at = $this->getAccessToken();
        if (! $at) {
            return null;
        }

        $baseUrl = $this->extractBaseUrl(PassportControl::accessTokenEndpoint());

        return Http::retry(3, 1000)
            ->withToken($at)
            ->baseUrl($baseUrl)
            ->withHeader('Accept', 'application/json');
    }

    /**
     * The introspection API is not a publicly accessible endpoint.
     * In order to access it, we need to authenticate with the introspection API.
     * This method returns the access token that we use to authenticate with the introspection API and possible other APIs
     * This method supports caching and locking to avoid multiple requests for the same token
     * If the cache store does not support locking, we simply fetch a new token and cache it
     */
    protected function getAccessToken(): ?string
    {
        $cached = $this->getCachedAccessToken();
        if ($cached) {
            return $cached;
        }

        $store = Cache::store(PassportControl::cacheStore());

        if (! $store instanceof LockProvider) {
            return $this->fetchAndCacheAccessToken();
        }

        $lockKey = PassportControl::cachePrefix() . 'introspection_at_lock';
        $lock = $store->lock($lockKey, 10);

        try {
            if ($lock->get()) {
                try {
                    return $this->fetchAndCacheAccessToken();
                } finally {
                    $lock->release();
                }
            } else {
                $lock->block(5);

                return $this->getCachedAccessToken();
            }
        } catch (Throwable) {
        }

        return null;
    }

    private function getCachedAccessToken(): ?string
    {
        try {
            $accessToken = Cache::store(PassportControl::cacheStore())->get(PassportControl::cachePrefix() . 'introspection_at');
            if ($accessToken) {
                return Crypt::decryptString($accessToken);
            }
        } catch (Throwable) {
        }

        return null;
    }

    private function fetchAndCacheAccessToken(): ?string
    {
        try {
            $atResponse = Http::retry(3, 1000)
                ->withHeader('Accept', 'application/json')
                ->asJson()
                ->post(PassportControl::accessTokenEndpoint(), [
                    'grant_type' => 'client_credentials',
                    'client_id' => PassportControl::clientID(),
                    'client_secret' => PassportControl::clientSecret(),
                    'scope' => implode(' ', PassportControl::scopes()),
                ]);

            if ($atResponse->failed()) {
                return null;
            }

            $json = $atResponse->json();
            if (! isset($json['access_token'], $json['expires_in']) || ! $json) {
                return null;
            }

            $accessToken = $json['access_token'];

            try {
                Cache::store(PassportControl::cacheStore())->put(
                    PassportControl::cachePrefix() . 'introspection_at',
                    Crypt::encryptString($accessToken),
                    $json['expires_in'] - 60
                );
            } catch (Throwable) {
            }

            return $accessToken;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Extracts the base_url from the introspection URL config,
     * this is done, so we do not need to have an extra setting for the base url, and we avoid breaking changes
     * In the future we might add a separate setting for the base url
     */
    private function extractBaseUrl(string $url): string
    {
        $parsed = parse_url($url);

        $baseUrl = $parsed['scheme'] . '://' . $parsed['host'];

        if (isset($parsed['port'])) {
            $baseUrl .= ':' . $parsed['port'];
        }

        return $baseUrl;
    }
}
