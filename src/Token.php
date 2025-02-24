<?php

namespace XCoorp\PassportControl;

use Carbon\Carbon;
use XCoorp\PassportControl\Enums\CredentialType;
use XCoorp\PassportControl\Traits\ResolvesInheritedScopes;

class Token
{
    use ResolvesInheritedScopes;

    public function __construct(
        protected bool $active,
        protected array $scopes,
        protected string $clientId,
        protected string $userId,
        protected CredentialType $credentialType,
        protected Carbon $expiresAt,
        protected ?string $username = null,
        protected ?Carbon $issuedAt = null,
        protected ?Carbon $notBefore = null,
    ) {
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

    public function isActive(): bool
    {
        return $this->active;
    }

    public function client(): string
    {
        return $this->clientId;
    }

    public function user(): string
    {
        return $this->userId;
    }

    public function credentialType(): CredentialType
    {
        return $this->credentialType;
    }

    public function expiresAt(): Carbon
    {
        return $this->expiresAt;
    }

    public function username(): ?string
    {
        return $this->username;
    }

    public function issuedAt(): ?Carbon
    {
        return $this->issuedAt;
    }

    public function notBefore(): ?Carbon
    {
        return $this->notBefore;
    }
}
