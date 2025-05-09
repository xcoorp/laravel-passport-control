<?php

namespace XCoorp\PassportControl\Repositories;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Throwable;
use XCoorp\PassportControl\Contracts\TokenFactory;
use XCoorp\PassportControl\Contracts\Token;
use XCoorp\PassportControl\Contracts\TokenRepository as TokenRepositoryContract;
use XCoorp\PassportControl\PassportControl;

class TokenRepository implements TokenRepositoryContract
{
    public function __construct(
        protected TokenFactory $tokenFactory
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function introspect(string $token_id): ?Token
    {
        if (PassportControl::cacheIntrospectionResult() !== null) {
            try {
                $result = Cache::store(PassportControl::cacheStore())->get(PassportControl::cachePrefix().'tk_'.$token_id);
                if ($result) {
                    return $result;
                }
            } catch (Throwable) {
            }
        }

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

            $token = $this->tokenFactory->createToken($json);

            if (PassportControl::cacheIntrospectionResult() !== null && ($json['active'] ?? false)) {
                try {
                    Cache::store(PassportControl::cacheStore())->put(
                        PassportControl::cachePrefix().'tk_'.$token_id,
                        $token,
                        min($json['exp'] - time(), PassportControl::cacheIntrospectionResult())
                    );
                } catch (Throwable) {
                }
            }

            return $token;
        } catch (Throwable) {
        }

        return null;
    }


    /**
     * The introspection API is not a publicly accessible endpoint.
     * In order to access it, we need to authenticate with the introspection API.
     * This method returns the access token that we use to authenticate with the introspection API.
     */
    public function getAccessToken(): ?string
    {
        // We have this in a separate try-catch block, because we do not want to stop working if the cache fails.
        try {
            $accessToken = Cache::store(PassportControl::cacheStore())->get(PassportControl::cachePrefix().'introspection_at');
            if ($accessToken) {
                return Crypt::decryptString($accessToken);
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
                Crypt::encryptString($json['access_token']),
                $json['expires_in'] - 60
            );
        } catch (Throwable) {
        }

        return $accessToken;
    }
}
