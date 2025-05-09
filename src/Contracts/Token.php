<?php

namespace XCoorp\PassportControl\Contracts;

use Illuminate\Support\Carbon;

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

    public function expiresAt(): Carbon;

    public function issuedAt(): ?Carbon;

    public function notBefore(): ?Carbon;
}
