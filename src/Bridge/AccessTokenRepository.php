<?php

namespace XCoorp\PassportControl\Bridge;

use Illuminate\Contracts\Events\Dispatcher;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use XCoorp\PassportControl\Contracts\TokenRepository;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function __construct(
        protected TokenRepository $tokenRepository,
        protected Dispatcher $events
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessTokenEntityInterface
    {
        return new AccessToken($userIdentifier, $scopes, $clientEntity);
    }

    /**
     * Since we are only a resource server, we don't need to implement this method.
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void {}

    /**
     * Since we are only a resource server, we don't need to implement this method.
     */
    public function revokeAccessToken(string $tokenId): void {}

    public function isAccessTokenRevoked(string $tokenId): bool
    {
        return false;
    }
}
