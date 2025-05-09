<?php

namespace XCoorp\PassportControl;

use Illuminate\Support\Carbon;
use XCoorp\PassportControl\Contracts\Token as TokenContract;
use XCoorp\PassportControl\Traits\ResolvesInheritedScopes;

class Token implements TokenContract
{
    use ResolvesInheritedScopes;

    public function __construct(
        protected bool $active,
        protected array $scopes,
        protected string $clientId,
        protected string $userId,
        protected Carbon $expiresAt,
        protected ?string $username = null,
        protected ?Carbon $issuedAt = null,
        protected ?Carbon $notBefore = null,
    ) {
    }

    /**
     * {@inheritDoc}
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

    public function scopes(): array
    {
        return $this->scopes;
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
