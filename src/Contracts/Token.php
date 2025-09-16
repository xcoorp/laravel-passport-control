<?php

declare(strict_types=1);

namespace XCoorp\PassportControl\Contracts;

use Carbon\CarbonInterface;

interface Token
{
    /**
     * Determine if the token has a given scope.
     */
    public function can(string $scope): bool;

    public function isActive(): bool;

    public function client(): string;

    public function user(): string;

    public function username(): ?string;

    public function scopes(): array;

    public function expiresAt(): CarbonInterface;

    public function issuedAt(): ?CarbonInterface;

    public function notBefore(): ?CarbonInterface;
}
