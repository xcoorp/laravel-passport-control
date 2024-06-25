<?php

namespace XCoorp\PassportControl\Contracts;

use XCoorp\PassportControl\Token;

interface UserContract
{
    /**
     * The user's access token.
     */
    public function token(): ?Token;

    /**
     * Determine if the user has a given scope.
     */
    public function tokenCan(string $scope): bool;

    /**
     * Provide the user with the given access token.
     */
    public function withAccessToken(Token $accessToken): self;
}
