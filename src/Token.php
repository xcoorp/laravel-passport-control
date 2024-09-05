<?php

namespace XCoorp\PassportControl;

use Carbon\Carbon;
use XCoorp\PassportControl\Traits\ResolvesInheritedScopes;

class Token
{
    use ResolvesInheritedScopes;

    /**
     * The token's active status.
     */
    protected bool $active;

    /**
     * The token's scopes.
     */
    protected array $scopes;

    /**
     * The client ID.
     */
    protected string $clientId;

    /**
     * The unique oauth user identifier.
     */
    protected string $userId;

    /**
     * The token's expiration date.
     */
    protected Carbon $expiresAt;

    /**
     * Token constructor.
     */
    public function __construct(bool $active, array $scopes, string $clientId, string $userId, string $expiresAt)
    {
        $this->active = $active;
        $this->scopes = $scopes;
        $this->clientId = $clientId;
        $this->userId = $userId;
        $this->expiresAt = Carbon::createFromTimestamp($expiresAt);
    }

    /**
     * Determine if the token has a given scope.
     */
    public function can(string $scope): bool
    {
        if (in_array('*', $this->scopes)) {
            return true;
        }

        $scopes = PassportControl::withInheritedScopes()
            ? $this->resolveInheritedScopes($scope)
            : [$scope];

        foreach ($scopes as $scope) {
            if (in_array($scope, $this->scopes, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the token is missing a given scope.
     */
    public function cant(string $scope): bool
    {
        return ! $this->can($scope);
    }

    /**
     * Check if the token is active.
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Get the token's client ID.
     */
    public function client(): string
    {
        return $this->clientId;
    }

    /**
     * Get the token's user ID.
     */
    public function user(): string
    {
        return $this->userId;
    }

    /**
     * Get the token's expiration date.
     */
    public function expiresAt(): Carbon
    {
        return $this->expiresAt;
    }
}
