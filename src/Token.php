<?php

declare(strict_types=1);

namespace XCoorp\PassportControl;

use Carbon\CarbonInterface;
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
        protected CarbonInterface $expiresAt,
        protected ?string $username = null,
        protected ?CarbonInterface $issuedAt = null,
        protected ?CarbonInterface $notBefore = null,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function can(string $scope): bool
    {
        if (in_array('*', $this->scopes, true)) {
            return true;
        }

        $scopes = PassportControl::withInheritedScopes()
            ? $this->resolveInheritedScopes($scope)
            : [$scope];

        foreach ($scopes as $sc) {
            if (in_array($sc, $this->scopes, true)) {
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

    public function expiresAt(): CarbonInterface
    {
        return $this->expiresAt;
    }

    public function username(): ?string
    {
        return $this->username;
    }

    public function issuedAt(): ?CarbonInterface
    {
        return $this->issuedAt;
    }

    public function notBefore(): ?CarbonInterface
    {
        return $this->notBefore;
    }
}
