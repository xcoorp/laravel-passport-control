<?php

namespace XCoorp\PassportControl\Bridge;

use Illuminate\Contracts\Events\Dispatcher;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use XCoorp\PassportControl\Repositories\TokenRepository;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * The token repository instance.
     */
    protected TokenRepository $tokenRepository;

    /**
     * The event dispatcher instance.
     */
    protected Dispatcher $events;

    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(TokenRepository $tokenRepository, Dispatcher $events)
    {
        $this->events = $events;
        $this->tokenRepository = $tokenRepository;
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

    /**
     * Check if the access token has been revoked.
     */
    public function isAccessTokenRevoked(string $tokenId): bool
    {
        return $this->tokenRepository->isAccessTokenRevoked($tokenId);
    }
}
