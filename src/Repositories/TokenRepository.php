<?php

declare(strict_types=1);

namespace XCoorp\PassportControl\Repositories;

use Illuminate\Support\Facades\Cache;
use Throwable;
use XCoorp\PassportControl\Contracts\Client;
use XCoorp\PassportControl\Contracts\Token;
use XCoorp\PassportControl\Contracts\TokenFactory;
use XCoorp\PassportControl\Contracts\TokenRepository as TokenRepositoryContract;
use XCoorp\PassportControl\PassportControl;

class TokenRepository implements TokenRepositoryContract
{
    public function __construct(
        protected TokenFactory $tokenFactory,
        protected Client $authenticationServerService,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function introspect(string $token_id): ?Token
    {
        if (PassportControl::cacheIntrospectionResult() !== null) {
            try {
                $result = Cache::store(PassportControl::cacheStore())->get(PassportControl::cachePrefix() . 'tk_' . $token_id);
                if ($result) {
                    return $result;
                }
            } catch (Throwable) {
            }
        }

        try {
            $request = $this->authenticationServerService->request();
            if (! $request) {
                return null;
            }

            $atResponse = $request
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

            if (($json['active'] ?? false) && PassportControl::cacheIntrospectionResult() !== null) {
                try {
                    Cache::store(PassportControl::cacheStore())->put(
                        PassportControl::cachePrefix() . 'tk_' . $token_id,
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
}
